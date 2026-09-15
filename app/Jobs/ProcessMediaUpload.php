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
         * Idempotency:
         * إذا تمت المعالجة سابقًا فلا نعيدها.
         */
        if ($mediaUpload->status === 'ready') {
            return;
        }

        if (! in_array(
            $mediaUpload->status,
            ['completed', 'processing', 'failed'],
            true
        )) {
            return;
        }

        $sourcePath = $mediaUpload->path;

        if (! $sourcePath) {
            throw new RuntimeException(
                'Tus upload path is missing.'
            );
        }

        /*
         * تأكد أن المسار فعلاً صادر من tusd storage.
         */
        $tusDirectory = realpath(
            '/var/lib/tusd/uploads'
        );

        $realSourcePath = realpath(
            $sourcePath
        );

        if (! $tusDirectory) {
            throw new RuntimeException(
                'Tus upload directory does not exist.'
            );
        }

        if (! $realSourcePath) {
            throw new RuntimeException(
                'Uploaded file does not exist.'
            );
        }

        if (! str_starts_with(
            $realSourcePath,
            $tusDirectory . DIRECTORY_SEPARATOR
        )) {
            throw new RuntimeException(
                'Invalid tus upload path.'
            );
        }

        /*
         * تحقق من الحجم الحقيقي.
         */
        $actualSize = filesize(
            $realSourcePath
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

        /*
         * تحقق فعليًا من MIME بعد أن وصل الملف.
         *
         * لا نعتمد فقط على MIME القادم من المتصفح.
         */
        $finfo = new \finfo(
            FILEINFO_MIME_TYPE
        );

        $actualMime = $finfo->file(
            $realSourcePath
        );

        $extensions = [
            'video/mp4' => 'mp4',

            'video/quicktime' => 'mov',

            'video/webm' => 'webm',

            'video/x-matroska' => 'mkv',

            'video/x-msvideo' => 'avi',
        ];

        if (! isset(
            $extensions[$actualMime]
        )) {
            throw new RuntimeException(
                'Unsupported uploaded video type: '
                . $actualMime
            );
        }

        $extension = $extensions[$actualMime];

        /*
         * اسم deterministic.
         *
         * مفيد جدًا إذا أعاد Queue محاولة الـ Job،
         * فلا ينشئ اسمًا جديدًا كل مرة.
         */
        $filename =
            $mediaUpload->uuid
            . '.'
            . $extension;

        /*
         * هذا هو folder الجديد للبودكاست.
         *
         * إذا كان الفرونت القديم يستعمل اسمًا مختلفًا
         * مثل podcast بدل podcasts
         * غيّر هذا السطر فقط.
         */
        $folder = 'podcasts';

        $relativePath =
            $folder
            . '/'
            . $filename;

        $destinationPath =
            Storage::disk('public')
                ->path($relativePath);

        File::ensureDirectoryExists(
            dirname($destinationPath),
            0755,
            true
        );

        $mediaUpload->update([
            'status' => 'processing',
        ]);

        try {

            /*
             * rename هو الأفضل:
             *
             * إذا المصدر والوجهة على نفس filesystem
             * فإن نقل 5GB شبه فوري ولا ينسخ 5GB.
             */
            $moved = @rename(
                $realSourcePath,
                $destinationPath
            );

            /*
             * في حال /var/lib و /var/www
             * على filesystems مختلفين،
             * rename قد يفشل.
             *
             * نستخدم stream copy بدون تحميل 5GB
             * في RAM.
             */
            if (! $moved) {

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
                    $destinationPath,
                    'wb'
                );

                if (! $output) {
                    fclose($input);

                    throw new RuntimeException(
                        'Unable to create destination video.'
                    );
                }

                try {

                    $copied = stream_copy_to_stream(
                        $input,
                        $output
                    );

                    if ($copied === false) {
                        throw new RuntimeException(
                            'Failed while copying video.'
                        );
                    }

                } finally {

                    fclose($input);
                    fclose($output);
                }

                /*
                 * تحقق بعد النسخ.
                 */
                $destinationSize =
                    filesize($destinationPath);

                if (
                    (int) $destinationSize !==
                    (int) $actualSize
                ) {

                    @unlink(
                        $destinationPath
                    );

                    throw new RuntimeException(
                        'Copied video size mismatch.'
                    );
                }

                @unlink(
                    $realSourcePath
                );
            }

            /*
             * حذف tus .info sidecar.
             *
             * tusd local storage ينشئ ملف البيانات
             * وملف ID.info.
             */
            @unlink(
                $realSourcePath . '.info'
            );

            /*
             * ربط الفيديو بالـ Podcast.
             */
            if (
                $mediaUpload->model_type === 'podcast'
            ) {

                $podcast = Podcast::findOrFail(
                    $mediaUpload->model_id
                );

                $podcast->update([
                    'video' => $relativePath,
                ]);
            }

            /*
             * path الآن يتحول من tus temporary path
             * إلى Laravel public relative path.
             */
            $mediaUpload->update([
                'path' => $relativePath,

                'mime_type' => $actualMime,

                'uploaded_size' => $actualSize,

                'status' => 'ready',

                'error' => null,

                'failed_at' => null,
            ]);

        } catch (Throwable $e) {

            $mediaUpload->update([
                'status' => 'failed',

                'error' => $e->getMessage(),

                'failed_at' => now(),
            ]);

            throw $e;
        }
    }
}
