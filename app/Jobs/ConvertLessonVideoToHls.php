<?php

namespace App\Jobs;

use App\Models\Lesson;
use Illuminate\Bus\Queueable;
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

class ConvertLessonVideoToHls implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 7200;

    public int $tries = 2;

    public function __construct(
        public readonly int $lessonId
    ) {
        $this->onQueue('video');
    }

    public function handle(Filesystem $files): void
    {
        $lesson = Lesson::query()->findOrFail(
            $this->lessonId
        );

        $sourceDiskName = $lesson->video_source_disk
            ?: config('lesson_video.source_disk');

        $hlsDiskName = $lesson->hls_disk
            ?: config('lesson_video.hls_disk');

        if (!$lesson->video_source_path) {
            throw new RuntimeException(
                'Source video path is missing.'
            );
        }

        $sourceDisk = Storage::disk($sourceDiskName);
        $hlsDisk = Storage::disk($hlsDiskName);

        if (!$sourceDisk->exists($lesson->video_source_path)) {
            throw new RuntimeException(
                'Source video file does not exist.'
            );
        }

        $lesson->forceFill([
            'hls_status' => 'processing',
            'hls_error' => null,
            'hls_processed_at' => null,
        ])->save();

        $sourceAbsolutePath = $sourceDisk->path(
            $lesson->video_source_path
        );

        $temporaryRelativePath = sprintf(
            'tmp/lesson-%d-%s',
            $lesson->id,
            Str::uuid()
        );

        $finalRelativePath = sprintf(
            'lessons/%d',
            $lesson->id
        );

        $hlsDisk->makeDirectory(
            $temporaryRelativePath
        );

        $temporaryAbsolutePath = $hlsDisk->path(
            $temporaryRelativePath
        );

        $finalAbsolutePath = $hlsDisk->path(
            $finalRelativePath
        );

        $backupAbsolutePath = null;

        try {
            $probe = $this->probeVideo(
                $sourceAbsolutePath
            );

            $profiles = $this->profilesForHeight(
                $probe['height']
            );

            foreach (array_keys($profiles) as $profileName) {
                $hlsDisk->makeDirectory(
                    "{$temporaryRelativePath}/{$profileName}"
                );
            }

            $command = $this->buildFfmpegCommand(
                sourcePath: $sourceAbsolutePath,
                outputPath: $temporaryAbsolutePath,
                profiles: $profiles,
                hasAudio: $probe['has_audio'],
            );

            $process = new Process($command);

            $process->setTimeout(
                $this->timeout
            );

            $process->run();

            if (!$process->isSuccessful()) {
                throw new RuntimeException(
                    'FFmpeg failed: ' .
                    mb_substr(
                        $process->getErrorOutput(),
                        0,
                        8000
                    )
                );
            }

            $masterManifest = $temporaryAbsolutePath .
                '/master.m3u8';

            if (!is_file($masterManifest)) {
                throw new RuntimeException(
                    'FFmpeg did not create master.m3u8.'
                );
            }

            /*
             * تبديل المجلد بشكل شبه ذري حتى لا يظهر مجلد ناقص
             * أثناء إعادة معالجة الفيديو.
             */
            $files->ensureDirectoryExists(
                dirname($finalAbsolutePath)
            );

            if (is_dir($finalAbsolutePath)) {
                $backupAbsolutePath = $finalAbsolutePath .
                    '-old-' .
                    Str::uuid();

                if (!rename(
                    $finalAbsolutePath,
                    $backupAbsolutePath
                )) {
                    throw new RuntimeException(
                        'Could not create HLS backup.'
                    );
                }
            }

            if (!rename(
                $temporaryAbsolutePath,
                $finalAbsolutePath
            )) {
                if (
                    $backupAbsolutePath &&
                    is_dir($backupAbsolutePath)
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

            if (
                $backupAbsolutePath &&
                is_dir($backupAbsolutePath)
            ) {
                $files->deleteDirectory(
                    $backupAbsolutePath
                );
            }

            $lesson->forceFill([
                'hls_disk' => $hlsDiskName,
                'hls_path' => $finalRelativePath,
                'hls_status' => 'ready',
                'hls_error' => null,
                'hls_processed_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            if (
                isset($temporaryAbsolutePath) &&
                is_dir($temporaryAbsolutePath)
            ) {
                $files->deleteDirectory(
                    $temporaryAbsolutePath
                );
            }

            $lesson->forceFill([
                'hls_status' => 'failed',
                'hls_error' => mb_substr(
                    $exception->getMessage(),
                    0,
                    10000
                ),
            ])->save();

            throw $exception;
        }
    }

    /**
     * @return array{
     *     height: int,
     *     has_audio: bool
     * }
     */
    private function probeVideo(
        string $sourcePath
    ): array {
        $process = new Process([
            config('lesson_video.ffprobe'),
            '-v',
            'error',
            '-show_entries',
            'stream=codec_type,width,height',
            '-of',
            'json',
            $sourcePath,
        ]);

        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                'FFprobe failed: ' .
                $process->getErrorOutput()
            );
        }

        $result = json_decode(
            $process->getOutput(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $height = null;
        $hasAudio = false;

        foreach ($result['streams'] ?? [] as $stream) {
            if (
                ($stream['codec_type'] ?? null) === 'video' &&
                $height === null
            ) {
                $height = (int) (
                    $stream['height'] ?? 0
                );
            }

            if (
                ($stream['codec_type'] ?? null) === 'audio'
            ) {
                $hasAudio = true;
            }
        }

        if (!$height) {
            throw new RuntimeException(
                'No video stream was found.'
            );
        }

        return [
            'height' => $height,
            'has_audio' => $hasAudio,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function profilesForHeight(
        int $sourceHeight
    ): array {
        $configuredProfiles = config(
            'lesson_video.profiles',
            []
        );

        $profiles = [];

        foreach ($configuredProfiles as $name => $profile) {
            if (
                (int) $profile['height'] <= $sourceHeight
            ) {
                $profiles[$name] = $profile;
            }
        }

        /*
         * إذا كان المصدر أقل من 360p نستخدم أقل Profile متوفر.
         */
        if (
            empty($profiles) &&
            !empty($configuredProfiles)
        ) {
            $firstName = array_key_first(
                $configuredProfiles
            );

            $profiles[$firstName] =
                $configuredProfiles[$firstName];
        }

        if (empty($profiles)) {
            throw new RuntimeException(
                'No HLS profiles are configured.'
            );
        }

        return $profiles;
    }

    /**
     * @param array<string, array<string, mixed>> $profiles
     * @return array<int, string>
     */
    private function buildFfmpegCommand(
        string $sourcePath,
        string $outputPath,
        array $profiles,
        bool $hasAudio
    ): array {
        $segmentSeconds = max(
            2,
            (int) config(
                'lesson_video.segment_seconds',
                10
            )
        );

        $profileNames = array_keys($profiles);
        $profileCount = count($profiles);

        $splitOutputs = [];
        $filters = [];

        foreach (
            array_keys($profileNames)
            as $index
        ) {
            $splitOutputs[] = "[video{$index}]";
        }

        $filters[] = sprintf(
            '[0:v:0]split=%d%s',
            $profileCount,
            implode('', $splitOutputs)
        );

        foreach (
            array_values($profiles)
            as $index => $profile
        ) {
            $width = (int) $profile['width'];
            $height = (int) $profile['height'];

            $filters[] = sprintf(
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
            config('lesson_video.ffmpeg'),
            '-y',
            '-hide_banner',
            '-i',
            $sourcePath,
            '-filter_complex',
            implode(';', $filters),
        ];

        $variantMapParts = [];

        foreach (
            array_values($profiles)
            as $index => $profile
        ) {
            $profileName = $profileNames[$index];

            $command[] = '-map';
            $command[] = "[video{$index}out]";

            if ($hasAudio) {
                $command[] = '-map';
                $command[] = '0:a:0';
            }

            $command[] = "-c:v:{$index}";
            $command[] = 'libx264';

            $command[] = "-preset:v:{$index}";
            $command[] = 'veryfast';

            $command[] = "-profile:v:{$index}";
            $command[] = 'main';

            $command[] = "-level:v:{$index}";
            $command[] = '4.0';

            $command[] = "-pix_fmt:v:{$index}";
            $command[] = 'yuv420p';

            $command[] = "-b:v:{$index}";
            $command[] = (string) $profile[
                'video_bitrate'
            ];

            $command[] = "-maxrate:v:{$index}";
            $command[] = (string) $profile[
                'maxrate'
            ];

            $command[] = "-bufsize:v:{$index}";
            $command[] = (string) $profile[
                'bufsize'
            ];

            /*
             * Keyframe كل ثانيتين، وتقطيع HLS كل 10 ثوانٍ.
             * هذا يساعد في جعل حدود المقاطع متزامنة بين الجودات.
             */
            $command[] = "-force_key_frames:v:{$index}";
            $command[] = 'expr:gte(t,n_forced*2)';

            $command[] = "-sc_threshold:v:{$index}";
            $command[] = '0';

            if ($hasAudio) {
                $command[] = "-c:a:{$index}";
                $command[] = 'aac';

                $command[] = "-b:a:{$index}";
                $command[] = (string) $profile[
                    'audio_bitrate'
                ];

                $command[] = "-ac:a:{$index}";
                $command[] = '2';

                $command[] = "-ar:a:{$index}";
                $command[] = '48000';

                $variantMapParts[] = sprintf(
                    'v:%d,a:%d,name:%s',
                    $index,
                    $index,
                    $profileName
                );
            } else {
                $variantMapParts[] = sprintf(
                    'v:%d,name:%s',
                    $index,
                    $profileName
                );
            }
        }

        return array_merge($command, [
            '-f',
            'hls',

            '-hls_time',
            (string) $segmentSeconds,

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
            implode(' ', $variantMapParts),

            '-hls_segment_filename',
            $outputPath . '/%v/seg_%05d.ts',

            $outputPath . '/%v/index.m3u8',
        ]);
    }
}
