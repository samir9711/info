<?php

namespace App\Jobs;

use App\Models\Lesson;
use App\Models\MediaUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class ConvertLessonVideoToHls implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * FFmpeg قد يحتاج وقتًا طويلًا مع الفيديوهات الكبيرة.
     */
    public int $timeout = 7200;

    /**
     * عدد محاولات الـ Job.
     */
    public int $tries = 2;

    /**
     * في حال timeout اعتبر الـ Job فاشلة.
     */
    public bool $failOnTimeout = true;

    /**
     * منع تشغيل Job أخرى لنفس الدرس
     * لمدة أطول قليلًا من timeout.
     */
    public int $uniqueFor = 7500;

    public function __construct(
        public readonly int $lessonId,
        public readonly ?int $mediaUploadId = null
    ) {
        $this->onQueue('video');
    }

    /**
     * لا نريد عمليتي FFmpeg لنفس Lesson
     * تعملان في نفس الوقت.
     */
    public function uniqueId(): string
    {
        return sprintf(
            'lesson-hls:%d',
            $this->lessonId
        );
    }

    public function handle(
        Filesystem $files
    ): void {
        $lesson = Lesson::query()
            ->findOrFail(
                $this->lessonId
            );

        /**
         * mediaUploadId اختياري حتى تبقى
         * الـ Job متوافقة مع الاستدعاءات القديمة:
         *
         * ConvertLessonVideoToHls::dispatch($lessonId)
         *
         * والاستدعاء الجديد عبر tus:
         *
         * ConvertLessonVideoToHls::dispatch(
         *     $lessonId,
         *     $mediaUploadId
         * )
         */
        $mediaUpload = null;

        if ($this->mediaUploadId !== null) {
            $mediaUpload =
                MediaUpload::query()
                    ->findOrFail(
                        $this->mediaUploadId
                    );

            /**
             * حماية من تمرير MediaUpload
             * من نوع آخر.
             */
            if (
                $mediaUpload->model_type !==
                    'lesson' ||
                (int) $mediaUpload->model_id !==
                    (int) $lesson->id
            ) {
                throw new RuntimeException(
                    'Media upload does not belong to this lesson.'
                );
            }
        }

        $sourceDiskName =
            $lesson->video_source_disk
                ?: config(
                    'lesson_video.source_disk'
                );

        $hlsDiskName =
            $lesson->hls_disk
                ?: config(
                    'lesson_video.hls_disk'
                );

        if (
            ! is_string(
                $sourceDiskName
            ) ||
            $sourceDiskName === ''
        ) {
            throw new RuntimeException(
                'Lesson source disk is not configured.'
            );
        }

        if (
            ! is_string(
                $hlsDiskName
            ) ||
            $hlsDiskName === ''
        ) {
            throw new RuntimeException(
                'Lesson HLS disk is not configured.'
            );
        }

        if (! $lesson->video_source_path) {
            throw new RuntimeException(
                'Source video path is missing.'
            );
        }

        /**
         * =============================================
         * حماية من stale Job
         * =============================================
         *
         * ProcessMediaUpload يجب أن يكون قد وضع path
         * النهائي داخل MediaUpload وLesson.
         *
         * إذا لم يعودا متطابقين فهذا يعني أن هذا
         * Upload لم يعد هو الفيديو الحالي للدرس.
         *
         * لا نريد عندها أن نحول فيديو قديم ونستبدل
         * HLS الجديد.
         */
        if (
            $mediaUpload &&
            $mediaUpload->path &&
            $mediaUpload->path !==
                $lesson->video_source_path
        ) {
            $mediaUpload->update([
                'status' => 'failed',

                'error' =>
                    'Lesson source video no longer matches this upload.',

                'failed_at' =>
                    now(),
            ]);

            return;
        }

        $sourceDisk =
            Storage::disk(
                $sourceDiskName
            );

        $hlsDisk =
            Storage::disk(
                $hlsDiskName
            );

        /**
         * =============================================
         * Idempotency after successful HLS
         * =============================================
         *
         * مثال:
         *
         * HLS انتهت
         * Lesson أصبحت ready
         * ثم Worker crash قبل جعل MediaUpload ready
         *
         * في retry لا نعيد FFmpeg.
         */
        if (
            $mediaUpload &&
            $lesson->hls_status ===
                'ready' &&
            $lesson->hls_path
        ) {
            $existingMaster =
                trim(
                    $lesson->hls_path,
                    '/'
                ) .
                '/master.m3u8';

            if (
                $hlsDisk->exists(
                    $existingMaster
                ) &&
                $mediaUpload->path ===
                    $lesson->video_source_path
            ) {
                $mediaUpload->update([
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

        if (! $sourceDisk->exists(
            $lesson->video_source_path
        )) {
            $message =
                'Source video file does not exist.';

            if ($mediaUpload) {
                $mediaUpload->update([
                    'status' =>
                        'failed',

                    'error' =>
                        $message,

                    'failed_at' =>
                        now(),
                ]);
            }

            $lesson->forceFill([
                'hls_status' =>
                    'failed',

                'hls_error' =>
                    $message,
            ])->save();

            throw new RuntimeException(
                $message
            );
        }

        /**
         * بداية مرحلة HLS.
         */
        $lesson->forceFill([
            'hls_status' =>
                'processing',

            'hls_error' =>
                null,

            'hls_processed_at' =>
                null,
        ])->save();

        if ($mediaUpload) {
            $mediaUpload->update([
                'status' =>
                    'processing',

                'error' =>
                    null,

                'failed_at' =>
                    null,
            ]);
        }

        $sourceAbsolutePath =
            $sourceDisk->path(
                $lesson->video_source_path
            );

        /**
         * نكتب HLS الجديدة أولًا داخل tmp.
         *
         * لا نلمس HLS الحالية قبل نجاح FFmpeg.
         */
        $temporaryRelativePath =
            sprintf(
                'tmp/lesson-%d-%s',
                $lesson->id,
                Str::uuid()
            );

        /**
         * المسار النهائي يبقى كما كان سابقًا.
         *
         * هذا مهم جدًا لأن LessonVideoService
         * الحالي يعتمد على:
         *
         * lessons/{lessonId}
         */
        $finalRelativePath =
            sprintf(
                'lessons/%d',
                $lesson->id
            );

        $hlsDisk->makeDirectory(
            $temporaryRelativePath
        );

        $temporaryAbsolutePath =
            $hlsDisk->path(
                $temporaryRelativePath
            );

        $finalAbsolutePath =
            $hlsDisk->path(
                $finalRelativePath
            );

        $backupAbsolutePath = null;

        try {
            /**
             * FFprobe:
             *
             * - ارتفاع الفيديو
             * - وجود Audio Stream
             */
            $probe =
                $this->probeVideo(
                    $sourceAbsolutePath
                );

            /**
             * لا ننشئ جودة أعلى من المصدر.
             */
            $profiles =
                $this->profilesForHeight(
                    $probe['height']
                );

            foreach (
                array_keys(
                    $profiles
                )
                as $profileName
            ) {
                $hlsDisk->makeDirectory(
                    "{$temporaryRelativePath}/{$profileName}"
                );
            }

            /**
             * بناء FFmpeg command.
             */
            $command =
                $this->buildFfmpegCommand(
                    sourcePath:
                        $sourceAbsolutePath,

                    outputPath:
                        $temporaryAbsolutePath,

                    profiles:
                        $profiles,

                    hasAudio:
                        $probe['has_audio'],
                );

            $process =
                new Process(
                    $command
                );

            $process->setTimeout(
                $this->timeout
            );

            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException(
                    'FFmpeg failed: ' .
                    mb_substr(
                        $process
                            ->getErrorOutput(),
                        0,
                        8000
                    )
                );
            }

            /**
             * أهم ملف:
             *
             * master.m3u8
             */
            $masterManifest =
                $temporaryAbsolutePath .
                '/master.m3u8';

            if (! is_file(
                $masterManifest
            )) {
                throw new RuntimeException(
                    'FFmpeg did not create master.m3u8.'
                );
            }

            /**
             * =========================================
             * Stale check مرة ثانية
             * =========================================
             *
             * FFmpeg قد تعمل لدقائق/ساعات.
             *
             * قبل أن نستبدل HLS الحالية يجب التأكد
             * أن هذا Upload ما زال المصدر الحالي.
             */
            if ($mediaUpload) {
                $freshLesson =
                    Lesson::query()
                        ->findOrFail(
                            $lesson->id
                        );

                if (
                    $freshLesson
                        ->video_source_path !==
                    $mediaUpload->path
                ) {
                    throw new RuntimeException(
                        'Lesson source changed while HLS processing was running.'
                    );
                }

                $lesson =
                    $freshLesson;
            }

            /**
             * =========================================
             * Atomic-ish directory replacement
             * =========================================
             *
             * إذا كان هناك HLS قديمة:
             *
             * lessons/13
             *
             * نحولها مؤقتًا إلى:
             *
             * lessons/13-old-UUID
             *
             * ثم نضع الجديدة مكانها.
             */
            $files->ensureDirectoryExists(
                dirname(
                    $finalAbsolutePath
                )
            );

            if (
                is_dir(
                    $finalAbsolutePath
                )
            ) {
                $backupAbsolutePath =
                    $finalAbsolutePath .
                    '-old-' .
                    Str::uuid();

                if (! rename(
                    $finalAbsolutePath,
                    $backupAbsolutePath
                )) {
                    throw new RuntimeException(
                        'Could not create HLS backup.'
                    );
                }
            }

            /**
             * نقل HLS الجديدة إلى المسار النهائي.
             */
            if (! rename(
                $temporaryAbsolutePath,
                $finalAbsolutePath
            )) {
                /**
                 * إذا فشل النقل نعيد HLS القديمة.
                 */
                if (
                    $backupAbsolutePath &&
                    is_dir(
                        $backupAbsolutePath
                    )
                ) {
                    rename(
                        $backupAbsolutePath,
                        $finalAbsolutePath
                    );
                }

                throw new RuntimeException(
                    'Could not move the new HLS directory.'
                );
            }

            /**
             * الآن HLS الجديدة أصبحت final.
             *
             * نحذف backup القديمة.
             */
            if (
                $backupAbsolutePath &&
                is_dir(
                    $backupAbsolutePath
                )
            ) {
                $files->deleteDirectory(
                    $backupAbsolutePath
                );

                $backupAbsolutePath =
                    null;
            }

            /**
             * =========================================
             * Lesson ready
             * =========================================
             */
            $lesson->forceFill([
                'hls_disk' =>
                    $hlsDiskName,

                'hls_path' =>
                    $finalRelativePath,

                'hls_status' =>
                    'ready',

                'hls_error' =>
                    null,

                'hls_processed_at' =>
                    now(),
            ])->save();

            /**
             * =========================================
             * MediaUpload ready
             * =========================================
             *
             * هذه الإضافة الجديدة المهمة لـ tus.
             */
            if ($mediaUpload) {
                $mediaUpload->update([
                    'status' =>
                        'ready',

                    'error' =>
                        null,

                    'failed_at' =>
                        null,
                ]);
            }

        } catch (Throwable $exception) {
            /**
             * إزالة أي temporary HLS ناقصة.
             */
            if (
                isset(
                    $temporaryAbsolutePath
                ) &&
                is_dir(
                    $temporaryAbsolutePath
                )
            ) {
                $files->deleteDirectory(
                    $temporaryAbsolutePath
                );
            }

            /**
             * إذا كنا قد أنشأنا backup
             * واختفت final لأي سبب،
             * نرجع النسخة القديمة.
             */
            if (
                $backupAbsolutePath &&
                is_dir(
                    $backupAbsolutePath
                ) &&
                ! is_dir(
                    $finalAbsolutePath
                )
            ) {
                @rename(
                    $backupAbsolutePath,
                    $finalAbsolutePath
                );

                $backupAbsolutePath =
                    null;
            }

            /**
             * تحقق إن كانت هذه الـ Job ما زالت
             * تخص source الحالي.
             *
             * إذا أصبح هناك source أحدث، لا نريد
             * أن نجعل Lesson الجديدة failed بسبب
             * Job قديمة.
             */
            $stillCurrent = true;

            if ($mediaUpload) {
                $freshLesson =
                    Lesson::query()
                        ->find(
                            $this->lessonId
                        );

                $stillCurrent =
                    $freshLesson !== null &&
                    $freshLesson
                        ->video_source_path ===
                    $mediaUpload->path;
            }

            if ($stillCurrent) {
                $lesson->forceFill([
                    'hls_status' =>
                        'failed',

                    'hls_error' =>
                        mb_substr(
                            $exception
                                ->getMessage(),
                            0,
                            10000
                        ),
                ])->save();
            }

            /**
             * MediaUpload نفسها failed.
             */
            if ($mediaUpload) {
                $mediaUpload->update([
                    'status' =>
                        'failed',

                    'error' =>
                        mb_substr(
                            $exception
                                ->getMessage(),
                            0,
                            10000
                        ),

                    'failed_at' =>
                        now(),
                ]);
            }

            throw $exception;
        }
    }

    /**
     * يتم استدعاؤها إذا انتهت الـ Job نهائيًا بالفشل
     * بعد استنفاد محاولات Queue.
     *
     * مفيدة خصوصًا في حالات worker timeout.
     */
    public function failed(
        ?Throwable $exception
    ): void {
        if (
            $this->mediaUploadId ===
            null
        ) {
            return;
        }

        $mediaUpload =
            MediaUpload::query()
                ->find(
                    $this->mediaUploadId
                );

        if (! $mediaUpload) {
            return;
        }

        $message =
            $exception
                ? mb_substr(
                    $exception->getMessage(),
                    0,
                    10000
                )
                : 'Lesson video HLS job failed.';

        $mediaUpload->update([
            'status' =>
                'failed',

            'error' =>
                $message,

            'failed_at' =>
                now(),
        ]);

        /**
         * لا نغير Lesson إذا أصبحت تشير
         * إلى Upload أخرى أحدث.
         */
        $lesson =
            Lesson::query()
                ->find(
                    $this->lessonId
                );

        if (
            ! $lesson ||
            $lesson->video_source_path !==
                $mediaUpload->path
        ) {
            return;
        }

        $lesson->forceFill([
            'hls_status' =>
                'failed',

            'hls_error' =>
                $message,
        ])->save();
    }

    /**
     * FFprobe.
     *
     * @return array{
     *     height:int,
     *     has_audio:bool
     * }
     */
    private function probeVideo(
        string $sourcePath
    ): array {
        $process =
            new Process([
                config(
                    'lesson_video.ffprobe'
                ),

                '-v',
                'error',

                '-show_entries',
                'stream=codec_type,width,height',

                '-of',
                'json',

                $sourcePath,
            ]);

        $process->setTimeout(
            120
        );

        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'FFprobe failed: ' .
                $process
                    ->getErrorOutput()
            );
        }

        $result =
            json_decode(
                $process->getOutput(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

        $height = null;

        $hasAudio = false;

        foreach (
            $result['streams'] ?? []
            as $stream
        ) {
            if (
                (
                    $stream[
                        'codec_type'
                    ] ?? null
                ) === 'video' &&
                $height === null
            ) {
                $height =
                    (int) (
                        $stream[
                            'height'
                        ] ?? 0
                    );
            }

            if (
                (
                    $stream[
                        'codec_type'
                    ] ?? null
                ) === 'audio'
            ) {
                $hasAudio = true;
            }
        }

        if (! $height) {
            throw new RuntimeException(
                'No video stream was found.'
            );
        }

        return [
            'height' =>
                $height,

            'has_audio' =>
                $hasAudio,
        ];
    }

    /**
     * اختيار الجودات المناسبة
     * حسب ارتفاع Source.
     *
     * @return array<string,array<string,mixed>>
     */
    private function profilesForHeight(
        int $sourceHeight
    ): array {
        $configuredProfiles =
            config(
                'lesson_video.profiles',
                []
            );

        $profiles = [];

        foreach (
            $configuredProfiles
            as $name => $profile
        ) {
            if (
                (int) $profile[
                    'height'
                ] <= $sourceHeight
            ) {
                $profiles[
                    $name
                ] = $profile;
            }
        }

        /**
         * إذا Source أقل من 360p
         * نستخدم أقل Profile متوفر.
         */
        if (
            empty($profiles) &&
            ! empty(
                $configuredProfiles
            )
        ) {
            $firstName =
                array_key_first(
                    $configuredProfiles
                );

            $profiles[
                $firstName
            ] =
                $configuredProfiles[
                    $firstName
                ];
        }

        if (empty($profiles)) {
            throw new RuntimeException(
                'No HLS profiles are configured.'
            );
        }

        return $profiles;
    }

    /**
     * بناء FFmpeg Command.
     *
     * @param array<string,array<string,mixed>> $profiles
     * @return array<int,string>
     */
    private function buildFfmpegCommand(
        string $sourcePath,
        string $outputPath,
        array $profiles,
        bool $hasAudio
    ): array {
        /**
         * عندك حاليًا 10 ثوانٍ.
         */
        $segmentSeconds =
            max(
                2,
                (int) config(
                    'lesson_video.segment_seconds',
                    10
                )
            );

        $profileNames =
            array_keys(
                $profiles
            );

        $profileCount =
            count(
                $profiles
            );

        $splitOutputs = [];

        $filters = [];

        foreach (
            array_keys(
                $profileNames
            )
            as $index
        ) {
            $splitOutputs[] =
                "[video{$index}]";
        }

        /**
         * Split source video stream
         * بعدد الجودات المطلوبة.
         */
        $filters[] =
            sprintf(
                '[0:v:0]split=%d%s',
                $profileCount,
                implode(
                    '',
                    $splitOutputs
                )
            );

        /**
         * Scale لكل Profile.
         */
        foreach (
            array_values(
                $profiles
            )
            as $index => $profile
        ) {
            $width =
                (int) $profile[
                    'width'
                ];

            $height =
                (int) $profile[
                    'height'
                ];

            $filters[] =
                sprintf(
                    '[video%d]' .
                    'scale=w=%d:h=%d:' .
                    'force_original_aspect_ratio=decrease,' .
                    'pad=%d:%d:(ow-iw)/2:(oh-ih)/2,' .
                    'setsar=1[video%dout]',
                    $index,
                    $width,
                    $height,
                    $width,
                    $height,
                    $index
                );
        }

        $command = [
            config(
                'lesson_video.ffmpeg'
            ),

            '-y',

            '-hide_banner',

            '-i',
            $sourcePath,

            '-filter_complex',
            implode(
                ';',
                $filters
            ),
        ];

        $variantMapParts = [];

        foreach (
            array_values(
                $profiles
            )
            as $index => $profile
        ) {
            $profileName =
                $profileNames[
                    $index
                ];

            /**
             * Video stream.
             */
            $command[] =
                '-map';

            $command[] =
                "[video{$index}out]";

            /**
             * Audio stream.
             */
            if ($hasAudio) {
                $command[] =
                    '-map';

                $command[] =
                    '0:a:0';
            }

            /**
             * H264.
             */
            $command[] =
                "-c:v:{$index}";

            $command[] =
                'libx264';

            $command[] =
                "-preset:v:{$index}";

            $command[] =
                'veryfast';

            $command[] =
                "-profile:v:{$index}";

            $command[] =
                'main';

            $command[] =
                "-level:v:{$index}";

            $command[] =
                '4.0';

            $command[] =
                "-pix_fmt:v:{$index}";

            $command[] =
                'yuv420p';

            /**
             * Video bitrate.
             */
            $command[] =
                "-b:v:{$index}";

            $command[] =
                (string) $profile[
                    'video_bitrate'
                ];

            $command[] =
                "-maxrate:v:{$index}";

            $command[] =
                (string) $profile[
                    'maxrate'
                ];

            $command[] =
                "-bufsize:v:{$index}";

            $command[] =
                (string) $profile[
                    'bufsize'
                ];

            /**
             * Keyframe كل ثانيتين.
             *
             * Segment حوالي 10 ثوانٍ.
             *
             * هذا يساعد على تزامن المقاطع
             * بين الجودات.
             */
            $command[] =
                "-force_key_frames:v:{$index}";

            $command[] =
                'expr:gte(t,n_forced*2)';

            $command[] =
                "-sc_threshold:v:{$index}";

            $command[] =
                '0';

            if ($hasAudio) {
                $command[] =
                    "-c:a:{$index}";

                $command[] =
                    'aac';

                $command[] =
                    "-b:a:{$index}";

                $command[] =
                    (string) $profile[
                        'audio_bitrate'
                    ];

                $command[] =
                    "-ac:a:{$index}";

                $command[] =
                    '2';

                $command[] =
                    "-ar:a:{$index}";

                $command[] =
                    '48000';

                $variantMapParts[] =
                    sprintf(
                        'v:%d,a:%d,name:%s',
                        $index,
                        $index,
                        $profileName
                    );
            } else {
                $variantMapParts[] =
                    sprintf(
                        'v:%d,name:%s',
                        $index,
                        $profileName
                    );
            }
        }

        return array_merge(
            $command,
            [
                '-f',
                'hls',

                '-hls_time',
                (string)
                    $segmentSeconds,

                '-hls_list_size',
                '0',

                '-hls_playlist_type',
                'vod',

                '-hls_flags',
                'independent_segments',

                '-hls_allow_cache',
                '0',

                '-master_pl_name',
                'master.m3u8',

                '-var_stream_map',
                implode(
                    ' ',
                    $variantMapParts
                ),

                '-hls_segment_filename',
                $outputPath .
                    '/%v/seg_%05d.ts',

                $outputPath .
                    '/%v/index.m3u8',
            ]
        );
    }
}
