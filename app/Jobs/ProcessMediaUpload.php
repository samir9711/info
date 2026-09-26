<?php

namespace App\Jobs;

use App\Models\MediaUpload;
use App\Models\Podcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProcessMediaUpload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 7200;

    public bool $failOnTimeout = true;

    public int $backoff = 10;

    /**
     * MIME الحقيقي => extension النهائي.
     */
    private const VIDEO_EXTENSIONS = [
        'video/mp4' => 'mp4',
        'video/quicktime' => 'mov',
        'video/webm' => 'webm',
        'video/x-matroska' => 'mkv',
        'video/x-msvideo' => 'avi',
    ];

    public function __construct(
        public int $mediaUploadId
    ) {
    }

    public function handle(): void
    {
        $mediaUpload = MediaUpload::findOrFail(
            $this->mediaUploadId
        );

        /*
         * انتهت المعالجة سابقًا.
         */
        if ($mediaUpload->status === 'ready') {
            return;
        }

        /*
         * لا نعالج Session ما زالت pending/uploading.
         */
        if (! in_array(
            $mediaUpload->status,
            [
                'completed',
                'processing',
                'failed',
            ],
            true
        )) {
            return;
        }

        try {
            if (
                $mediaUpload->model_type !==
                'podcast'
            ) {
                throw new RuntimeException(
                    'Unsupported media upload model type.'
                );
            }

            /*
             * مجلد tus المؤقت.
             */
            $tusDirectory = realpath(
                '/var/lib/tusd/uploads'
            );

            if (! $tusDirectory) {
                throw new RuntimeException(
                    'Tus upload directory does not exist.'
                );
            }

            /*
             * نحاول أولًا إيجاد الملف داخل tus.
             *
             * لكن في retry محتمل أن يكون الملف
             * قد نُقل بالفعل إلى storage/app/public.
             */
            $sourcePath = $mediaUpload->path;

            $realSourcePath = null;

            if (
                is_string($sourcePath) &&
                $sourcePath !== '' &&
                str_starts_with(
                    $sourcePath,
                    '/'
                )
            ) {
                $resolved = realpath(
                    $sourcePath
                );

                if ($resolved !== false) {
                    $realSourcePath =
                        $resolved;
                }
            }

            /*
             * =========================================
             * CASE 1:
             * الملف ما زال موجودًا داخل tusd.
             * =========================================
             */
            if ($realSourcePath) {
                if (! str_starts_with(
                    $realSourcePath,
                    $tusDirectory .
                        DIRECTORY_SEPARATOR
                )) {
                    throw new RuntimeException(
                        'Invalid tus upload path.'
                    );
                }

                [
                    $actualSize,
                    $actualMime,
                    $extension,
                ] = $this->inspectVideo(
                    $realSourcePath,
                    $mediaUpload
                );

                $relativePath =
                    'podcasts/' .
                    $mediaUpload->uuid .
                    '.' .
                    $extension;

                $destinationPath =
                    Storage::disk('public')
                        ->path(
                            $relativePath
                        );

                File::ensureDirectoryExists(
                    dirname(
                        $destinationPath
                    ),
                    0755,
                    true
                );

                $mediaUpload->update([
                    'status' =>
                        'processing',
                ]);

                /*
                 * ربما retry حصل بعد إنشاء
                 * الملف النهائي بالفعل.
                 */
                if (
                    is_file(
                        $destinationPath
                    ) &&
                    (int) filesize(
                        $destinationPath
                    ) ===
                    (int) $mediaUpload->size
                ) {
                    /*
                     * الملف النهائي موجود وصحيح،
                     * لا نعيد نسخه.
                     */
                    @unlink(
                        $realSourcePath
                    );

                    @unlink(
                        $realSourcePath .
                        '.info'
                    );
                } else {
                    /*
                     * إن وجد ملف ناقص من محاولة
                     * سابقة نحذفه.
                     */
                    if (
                        is_file(
                            $destinationPath
                        )
                    ) {
                        @unlink(
                            $destinationPath
                        );
                    }

                    /*
                     * الأفضل rename لأنه على نفس
                     * filesystem عندك.
                     */
                    $moved = @rename(
                        $realSourcePath,
                        $destinationPath
                    );

                    /*
                     * fallback إذا تغيّر filesystem
                     * في المستقبل.
                     */
                    if (! $moved) {
                        $temporaryDestination =
                            $destinationPath .
                            '.part';

                        @unlink(
                            $temporaryDestination
                        );

                        $input = fopen(
                            $realSourcePath,
                            'rb'
                        );

                        if (! $input) {
                            throw new RuntimeException(
                                'Unable to open source video.'
                            );
                        }

                        $output = fopen(
                            $temporaryDestination,
                            'wb'
                        );

                        if (! $output) {
                            fclose($input);

                            throw new RuntimeException(
                                'Unable to create temporary destination video.'
                            );
                        }

                        try {
                            $copied =
                                stream_copy_to_stream(
                                    $input,
                                    $output
                                );

                            if (
                                $copied === false
                            ) {
                                throw new RuntimeException(
                                    'Failed while copying video.'
                                );
                            }
                        } finally {
                            fclose($input);
                            fclose($output);
                        }

                        $temporarySize =
                            filesize(
                                $temporaryDestination
                            );

                        if (
                            $temporarySize ===
                                false ||
                            (int)
                                $temporarySize !==
                            (int)
                                $actualSize
                        ) {
                            @unlink(
                                $temporaryDestination
                            );

                            throw new RuntimeException(
                                'Copied video size mismatch.'
                            );
                        }

                        /*
                         * atomic final rename.
                         */
                        if (! @rename(
                            $temporaryDestination,
                            $destinationPath
                        )) {
                            @unlink(
                                $temporaryDestination
                            );

                            throw new RuntimeException(
                                'Could not finalize copied video.'
                            );
                        }

                        @unlink(
                            $realSourcePath
                        );
                    }

                    /*
                     * حذف tus sidecar.
                     */
                    @unlink(
                        $realSourcePath .
                        '.info'
                    );
                }

                /*
                 * تحقق نهائي بعد النقل.
                 */
                [
                    $actualSize,
                    $actualMime,
                    $extension,
                ] = $this->inspectVideo(
                    $destinationPath,
                    $mediaUpload
                );
            }

            /*
             * =========================================
             * CASE 2:
             * tus source اختفى، غالبًا بسبب retry
             * بعد نجاح rename وقبل تحديث DB.
             * =========================================
             */
            else {
                $existing =
                    $this->findExistingFinalVideo(
                        $mediaUpload
                    );

                if (! $existing) {
                    throw new RuntimeException(
                        'Uploaded file does not exist in tus storage or final storage.'
                    );
                }

                $relativePath =
                    $existing['relative_path'];

                $destinationPath =
                    $existing[
                        'absolute_path'
                    ];

                [
                    $actualSize,
                    $actualMime,
                    $extension,
                ] = $this->inspectVideo(
                    $destinationPath,
                    $mediaUpload
                );
            }

            /*
             * =========================================
             * Podcast update
             * =========================================
             */
            $podcast =
                Podcast::findOrFail(
                    $mediaUpload->model_id
                );

            /*
             * تأكد أن Podcast تشير إلى MP4
             * الحالي.
             */
            if (
                $podcast->video !==
                $relativePath
            ) {
                $podcast->update([
                    'video' =>
                        $relativePath,
                ]);
            }

            /*
             * المسار المتوقع لهذا Upload من HLS.
             */
            $expectedHlsPath =
                sprintf(
                    'podcast-hls/podcasts/%d/%s',
                    $podcast->id,
                    $mediaUpload->uuid
                );

            /*
             * إذا Retry وصل هنا بعد أن HLS
             * انتهت أصلًا، لا نعيد التحويل.
             */
            if (
                $podcast->hls_status ===
                    'ready' &&
                $podcast->hls_path ===
                    $expectedHlsPath
            ) {
                $mediaUpload->update([
                    'path' =>
                        $relativePath,

                    'mime_type' =>
                        $actualMime,

                    'uploaded_size' =>
                        $actualSize,

                    'status' =>
                        'ready',

                    'error' => null,

                    'failed_at' => null,
                ]);

                return;
            }

            /*
             * إذا Job الـ HLS تعمل حاليًا
             * لنفس الفيديو، لا نطلق واحدة أخرى.
             */
            if (
                $podcast->video ===
                    $relativePath &&
                $podcast->hls_status ===
                    'processing'
            ) {
                $mediaUpload->update([
                    'path' =>
                        $relativePath,

                    'mime_type' =>
                        $actualMime,

                    'uploaded_size' =>
                        $actualSize,

                    'status' =>
                        'processing',

                    'error' => null,

                    'failed_at' => null,
                ]);

                return;
            }

            /*
             * تجهيز Podcast لمرحلة HLS.
             *
             * لا نمسح hls_path هنا لأن Job
             * تحتاج المسار القديم حتى تحذفه
             * بعد نجاح النسخة الجديدة.
             */
            $podcast->forceFill([
                'hls_disk' =>
                    'public',

                'hls_status' =>
                    'pending',

                'hls_error' =>
                    null,

                'hls_processed_at' =>
                    null,
            ])->save();

            /*
             * MP4 جاهز، لكن HLS لم تنتهِ.
             *
             * لذلك status تبقى processing.
             */
            $mediaUpload->update([
                'path' =>
                    $relativePath,

                'mime_type' =>
                    $actualMime,

                'uploaded_size' =>
                    $actualSize,

                'status' =>
                    'processing',

                'error' => null,

                'failed_at' => null,
            ]);

            /*
             * المرحلة الثقيلة تنتقل إلى
             * video queue.
             */
            ConvertPodcastVideoToHls::dispatch(
                $podcast->id,
                $mediaUpload->id
            );

        } catch (Throwable $e) {
            $mediaUpload->update([
                'status' =>
                    'failed',

                'error' =>
                    mb_substr(
                        $e->getMessage(),
                        0,
                        10000
                    ),

                'failed_at' =>
                    now(),
            ]);

            throw $e;
        }
    }

    /**
     * فحص حجم الملف وMIME الحقيقي.
     *
     * @return array{0:int,1:string,2:string}
     */
    private function inspectVideo(
        string $absolutePath,
        MediaUpload $mediaUpload
    ): array {
        if (! is_file(
            $absolutePath
        )) {
            throw new RuntimeException(
                'Video file does not exist.'
            );
        }

        $actualSize =
            filesize(
                $absolutePath
            );

        if ($actualSize === false) {
            throw new RuntimeException(
                'Unable to determine uploaded file size.'
            );
        }

        if (
            (int) $actualSize !==
            (int) $mediaUpload->size
        ) {
            throw new RuntimeException(
                'Uploaded file size does not match expected size.'
            );
        }

        $finfo = new \finfo(
            FILEINFO_MIME_TYPE
        );

        $actualMime =
            $finfo->file(
                $absolutePath
            );

        if (
            ! is_string(
                $actualMime
            ) ||
            ! isset(
                self::VIDEO_EXTENSIONS[
                    $actualMime
                ]
            )
        ) {
            throw new RuntimeException(
                'Unsupported uploaded video type: ' .
                (
                    is_string(
                        $actualMime
                    )
                        ? $actualMime
                        : 'unknown'
                )
            );
        }

        return [
            (int) $actualSize,

            $actualMime,

            self::VIDEO_EXTENSIONS[
                $actualMime
            ],
        ];
    }

    /**
     * البحث عن الملف النهائي عند retry
     * إذا اختفى tus source بعد rename.
     *
     * @return array{
     *     relative_path:string,
     *     absolute_path:string
     * }|null
     */
    private function findExistingFinalVideo(
        MediaUpload $mediaUpload
    ): ?array {
        $disk =
            Storage::disk(
                'public'
            );

        /*
         * إذا DB تحولت أصلًا إلى relative path.
         */
        if (
            is_string(
                $mediaUpload->path
            ) &&
            $mediaUpload->path !== '' &&
            ! str_starts_with(
                $mediaUpload->path,
                '/'
            ) &&
            $disk->exists(
                $mediaUpload->path
            )
        ) {
            return [
                'relative_path' =>
                    $mediaUpload->path,

                'absolute_path' =>
                    $disk->path(
                        $mediaUpload->path
                    ),
            ];
        }

        /*
         * وإلا نبحث بالـ deterministic UUID.
         */
        foreach (
            array_values(
                self::VIDEO_EXTENSIONS
            )
            as $extension
        ) {
            $relativePath =
                'podcasts/' .
                $mediaUpload->uuid .
                '.' .
                $extension;

            if (
                ! $disk->exists(
                    $relativePath
                )
            ) {
                continue;
            }

            $absolutePath =
                $disk->path(
                    $relativePath
                );

            $size =
                filesize(
                    $absolutePath
                );

            if (
                $size !== false &&
                (int) $size ===
                (int)
                    $mediaUpload->size
            ) {
                return [
                    'relative_path' =>
                        $relativePath,

                    'absolute_path' =>
                        $absolutePath,
                ];
            }
        }

        return null;
    }
}
