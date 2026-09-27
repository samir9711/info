<?php

namespace App\Jobs;

use App\Models\Lesson;
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
        'video/ogg' => 'ogv',
        'application/ogg' => 'ogv',
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

        /**
         * انتهت المعالجة سابقًا.
         */
        if ($mediaUpload->status === 'ready') {
            return;
        }

        /**
         * لا نعالج Session ما زالت:
         *
         * pending
         * uploading
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
            /**
             * الأنواع المدعومة حاليًا.
             */
            if (! in_array(
                $mediaUpload->model_type,
                [
                    'podcast',
                    'lesson',
                ],
                true
            )) {
                throw new RuntimeException(
                    'Unsupported media upload model type.'
                );
            }

            /**
             * تحديد Disk + Folder النهائي
             * حسب نوع الفيديو.
             */
            [
                $destinationDiskName,
                $folder,
            ] = $this->destinationFor(
                $mediaUpload
            );

            $destinationDisk = Storage::disk(
                $destinationDiskName
            );

            /**
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

            /**
             * في post-finish يكون path عبارة عن
             * absolute path داخل /var/lib/tusd/uploads.
             *
             * في retry قد يكون path أصبح relative path
             * بعد أن نقلناه إلى final storage.
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
                    $realSourcePath = $resolved;
                }
            }

            /**
             * ==================================================
             * CASE 1
             *
             * الملف ما زال موجودًا داخل tusd.
             * ==================================================
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

                /**
                 * Podcast:
                 *
                 * podcasts/{uuid}.mp4
                 *
                 * Lesson:
                 *
                 * lessons/{lessonId}/{uuid}.mp4
                 */
                $relativePath =
                    $folder .
                    '/' .
                    $mediaUpload->uuid .
                    '.' .
                    $extension;

                $destinationPath =
                    $destinationDisk->path(
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
                    'status' => 'processing',
                ]);

                /**
                 * إذا Retry حدث بعد إنشاء
                 * final file وقبل تحديث DB.
                 */
                if (
                    is_file($destinationPath) &&
                    filesize($destinationPath) !== false &&
                    (int) filesize($destinationPath) ===
                        (int) $mediaUpload->size
                ) {
                    /**
                     * نتأكد أن final file نفسه
                     * فيديو صحيح.
                     */
                    [
                        $actualSize,
                        $actualMime,
                        $extension,
                    ] = $this->inspectVideo(
                        $destinationPath,
                        $mediaUpload
                    );

                    /**
                     * لا نحتاج المصدر المؤقت بعد الآن.
                     */
                    @unlink(
                        $realSourcePath
                    );

                    @unlink(
                        $realSourcePath .
                        '.info'
                    );
                } else {
                    /**
                     * إذا كان هناك final file ناقص
                     * من محاولة سابقة نحذفه.
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

                    /**
                     * نفس filesystem عندك حاليًا،
                     * لذلك rename هي الأسرع.
                     */
                    $moved = @rename(
                        $realSourcePath,
                        $destinationPath
                    );

                    /**
                     * Fallback:
                     *
                     * إذا أصبح source/destination
                     * على filesystem مختلف مستقبلًا.
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

                        /**
                         * تحقق من الحجم قبل جعل
                         * .part هو الملف النهائي.
                         */
                        $temporarySize =
                            filesize(
                                $temporaryDestination
                            );

                        if (
                            $temporarySize ===
                                false ||
                            (int) $temporarySize !==
                                (int) $actualSize
                        ) {
                            @unlink(
                                $temporaryDestination
                            );

                            throw new RuntimeException(
                                'Copied video size mismatch.'
                            );
                        }

                        /**
                         * Atomic finalization.
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

                    /**
                     * حذف tus sidecar.
                     */
                    @unlink(
                        $realSourcePath .
                        '.info'
                    );

                    /**
                     * تحقق نهائي من الملف
                     * بعد النقل.
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
            }

            /**
             * ==================================================
             * CASE 2
             *
             * tus source اختفى.
             *
             * هذا يمكن أن يحدث إذا:
             *
             * rename نجح
             * ثم Worker crash
             * قبل تحديث DB.
             *
             * نبحث عن final deterministic file.
             * ==================================================
             */
            else {
                $existing =
                    $this->findExistingFinalVideo(
                        $mediaUpload,
                        $destinationDiskName,
                        $folder
                    );

                if (! $existing) {
                    throw new RuntimeException(
                        'Uploaded file does not exist in tus storage or final storage.'
                    );
                }

                $relativePath =
                    $existing[
                        'relative_path'
                    ];

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

            /**
             * ==================================================
             * FINALIZATION
             * ==================================================
             */

            if (
                $mediaUpload->model_type ===
                'podcast'
            ) {
                $this->finalizePodcastUpload(
                    $mediaUpload,
                    $relativePath,
                    $actualMime,
                    $actualSize
                );

                return;
            }

            /**
             * Lesson
             */
            $this->finalizeLessonUpload(
                $mediaUpload,
                $relativePath,
                $actualMime,
                $actualSize,
                $destinationDiskName
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
     * ======================================================
     * PODCAST FINALIZATION
     * ======================================================
     */
    private function finalizePodcastUpload(
        MediaUpload $mediaUpload,
        string $relativePath,
        string $actualMime,
        int $actualSize
    ): void {
        $podcast =
            Podcast::query()
                ->findOrFail(
                    $mediaUpload->model_id
                );

        /**
         * ربط MP4 النهائي بالـ Podcast.
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

        /**
         * Podcast HLS له مسار خاص
         * بكل Upload UUID.
         */
        $expectedHlsPath =
            sprintf(
                'podcast-hls/podcasts/%d/%s',
                $podcast->id,
                $mediaUpload->uuid
            );

        /**
         * Retry بعد نجاح HLS.
         */
        if (
            $podcast->hls_status ===
                'ready' &&
            $podcast->hls_path ===
                $expectedHlsPath
        ) {
            $masterPath =
                trim(
                    $expectedHlsPath,
                    '/'
                ) .
                '/master.m3u8';

            if (
                Storage::disk('public')
                    ->exists(
                        $masterPath
                    )
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

                    'error' =>
                        null,

                    'failed_at' =>
                        null,
                ]);

                return;
            }
        }

        /**
         * إذا HLS Job تعمل بالفعل
         * لنفس الفيديو لا نطلق واحدة ثانية.
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

                'error' =>
                    null,

                'failed_at' =>
                    null,
            ]);

            return;
        }

        /**
         * تجهيز Podcast للـ HLS.
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

        /**
         * MP4 جاهز لكن HLS لم تنته بعد.
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

            'error' =>
                null,

            'failed_at' =>
                null,
        ]);

        ConvertPodcastVideoToHls::dispatch(
            $podcast->id,
            $mediaUpload->id
        );
    }

    /**
     * ======================================================
     * LESSON FINALIZATION
     * ======================================================
     */
    private function finalizeLessonUpload(
        MediaUpload $mediaUpload,
        string $relativePath,
        string $actualMime,
        int $actualSize,
        string $sourceDiskName
    ): void {
        $lesson =
            Lesson::query()
                ->findOrFail(
                    $mediaUpload->model_id
                );

        /**
         * ==================================================
         * RETRY AFTER SUCCESS
         *
         * إذا هذا هو نفس source الحالي للدرس
         * و HLS أصبحت ready بالفعل،
         * فلا نعيد FFmpeg.
         * ==================================================
         */
        if (
            $lesson->video_source_disk ===
                $sourceDiskName &&
            $lesson->video_source_path ===
                $relativePath &&
            $lesson->hls_status ===
                'ready' &&
            $lesson->hls_path
        ) {
            $hlsDiskName =
                $lesson->hls_disk
                    ?: config(
                        'lesson_video.hls_disk'
                    );

            $masterPath =
                trim(
                    $lesson->hls_path,
                    '/'
                ) .
                '/master.m3u8';

            if (
                Storage::disk(
                    $hlsDiskName
                )->exists(
                    $masterPath
                )
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

                    'error' =>
                        null,

                    'failed_at' =>
                        null,
                ]);

                return;
            }
        }

        /**
         * ==================================================
         * HLS JOB ALREADY RUNNING
         * ==================================================
         */
        if (
            $lesson->video_source_disk ===
                $sourceDiskName &&
            $lesson->video_source_path ===
                $relativePath &&
            $lesson->hls_status ===
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

                'error' =>
                    null,

                'failed_at' =>
                    null,
            ]);

            return;
        }

        /**
         * احتفظ بمصدر الفيديو القديم
         * حتى نربط المصدر الجديد.
         */
        $oldSourceDisk =
            $lesson->video_source_disk;

        $oldSourcePath =
            $lesson->video_source_path;

        /**
         * المصدر الجديد للدرس.
         */
        $lesson->forceFill([
            'video_source_disk' =>
                $sourceDiskName,

            'video_source_path' =>
                $relativePath,

            'hls_disk' =>
                config(
                    'lesson_video.hls_disk'
                ),

            /**
             * لا نمسح hls_path.
             *
             * ConvertLessonVideoToHls الحالية
             * ستستبدل HLS القديم فقط
             * بعد نجاح التحويل الجديد.
             */
            'hls_status' =>
                'pending',

            'hls_error' =>
                null,

            'hls_processed_at' =>
                null,
        ])->save();

        /**
         * Source upload انتهى،
         * لكن HLS لم تنته بعد.
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

            'error' =>
                null,

            'failed_at' =>
                null,
        ]);

        /**
         * تحويل الدرس إلى HLS.
         *
         * سنعدل ConvertLessonVideoToHls
         * في الخطوة التالية لتقبل
         * mediaUploadId أيضًا.
         */
        ConvertLessonVideoToHls::dispatch(
            $lesson->id,
            $mediaUpload->id
        );

        /**
         * حذف المصدر القديم بعد أن
         * أصبح المصدر الجديد محفوظًا
         * ومربوطًا بالدرس.
         *
         * فشل Cleanup لا يفشل Upload الجديد.
         */
        if (
            $oldSourceDisk &&
            $oldSourcePath &&
            (
                $oldSourceDisk !==
                    $sourceDiskName ||
                $oldSourcePath !==
                    $relativePath
            )
        ) {
            try {
                Storage::disk(
                    $oldSourceDisk
                )->delete(
                    $oldSourcePath
                );
            } catch (Throwable) {
                // Cleanup failure must not fail the upload.
            }
        }
    }

    /**
     * تحديد مكان حفظ الفيديو النهائي
     * حسب نوع الـ MediaUpload.
     *
     * @return array{0:string,1:string}
     */
    private function destinationFor(
        MediaUpload $mediaUpload
    ): array {
        if (
            $mediaUpload->model_type ===
            'podcast'
        ) {
            return [
                'public',
                'podcasts',
            ];
        }

        if (
            $mediaUpload->model_type ===
            'lesson'
        ) {
            $diskName = config(
                'lesson_video.source_disk'
            );

            if (
                ! is_string($diskName) ||
                $diskName === ''
            ) {
                throw new RuntimeException(
                    'Lesson source disk is not configured.'
                );
            }

            return [
                $diskName,

                'lessons/' .
                    $mediaUpload->model_id,
            ];
        }

        throw new RuntimeException(
            'Unsupported media upload model type.'
        );
    }

    /**
     * فحص:
     *
     * - وجود الملف
     * - الحجم
     * - MIME الحقيقي
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

        $finfo =
            new \finfo(
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
     * البحث عن final file في Retry
     * إذا اختفى tus source بعد rename.
     *
     * @return array{
     *     relative_path:string,
     *     absolute_path:string
     * }|null
     */
    private function findExistingFinalVideo(
        MediaUpload $mediaUpload,
        string $diskName,
        string $folder
    ): ?array {
        $disk =
            Storage::disk(
                $diskName
            );

        /**
         * DB قد تحتوي بالفعل
         * على relative final path.
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
            $absolutePath =
                $disk->path(
                    $mediaUpload->path
                );

            $size =
                filesize(
                    $absolutePath
                );

            if (
                $size !== false &&
                (int) $size ===
                    (int) $mediaUpload->size
            ) {
                return [
                    'relative_path' =>
                        $mediaUpload->path,

                    'absolute_path' =>
                        $absolutePath,
                ];
            }
        }

        /**
         * وإلا نبحث بالمسار deterministic:
         *
         * {folder}/{uuid}.{ext}
         */
        foreach (
            array_unique(
                array_values(
                    self::VIDEO_EXTENSIONS
                )
            )
            as $extension
        ) {
            $relativePath =
                $folder .
                '/' .
                $mediaUpload->uuid .
                '.' .
                $extension;

            if (! $disk->exists(
                $relativePath
            )) {
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
                    (int) $mediaUpload->size
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
