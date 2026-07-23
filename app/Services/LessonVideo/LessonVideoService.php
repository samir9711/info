<?php

namespace App\Services\LessonVideo;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class LessonVideoService
{
    public function __construct(
        private readonly Repository $cache,
        private readonly UrlGenerator $url
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function createSession(
        Lesson $lesson,
        User $user,
        Request $request
    ): array {
        $this->assertVideoReady($lesson);

        /*
         * أقوى من UUID للاستخدام كـsession secret.
         */
        $playbackSessionId = Str::random(64);

        $absoluteExpiresAt = now()->addMinutes(
            (int) config(
                'lesson_video.session_max_minutes',
                120
            )
        );

        $session = [
            'user_id' => (int) $user->id,
            'lesson_id' => (int) $lesson->id,

            'created_at' => now()->timestamp,

            'absolute_expires_at' =>
                $absoluteExpiresAt->timestamp,

            'user_agent_hash' =>
                $this->userAgentHash($request),

            'ip_hash' =>
                $this->ipHash($request),
        ];

        if (
            config(
                'lesson_video.single_session_per_lesson',
                true
            )
        ) {
            $activeKey = $this->activeSessionCacheKey(
                $user->id,
                $lesson->id
            );

            $oldPlaybackSessionId =
                $this->cache->get($activeKey);

            if (is_string($oldPlaybackSessionId)) {
                $this->cache->forget(
                    $this->playbackCacheKey(
                        $oldPlaybackSessionId
                    )
                );
            }

            $this->cache->put(
                $activeKey,
                $playbackSessionId,
                $absoluteExpiresAt
            );
        }

        $this->storeSession(
            $playbackSessionId,
            $session
        );

        return [
            'manifest_url' => $this->url->route(
                'user.lessons.video.hls.master',
                [
                    'lesson' => $lesson->id,
                    'psid' => $playbackSessionId,
                ]
            ),

            'playback_session_id' =>
                $playbackSessionId,

            'session_expires_at' =>
                $absoluteExpiresAt
                    ->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function refreshSession(
        string $playbackSessionId,
        Lesson $lesson,
        User $user,
        Request $request
    ): array {
        $session = $this->validatePlaybackSession(
            $playbackSessionId,
            $lesson,
            $user,
            $request
        );

        return [
            'manifest_url' => $this->url->route(
                'user.api.lessons.video.hls.master',
                [
                    'lesson' => $lesson->id,
                    'psid' => $playbackSessionId,
                ]
            ),

            'playback_session_id' =>
                $playbackSessionId,

            'session_expires_at' =>
                Carbon::createFromTimestamp(
                    (int) $session[
                        'absolute_expires_at'
                    ]
                )->toIso8601String(),
        ];
    }

    public function masterManifest(
        string $playbackSessionId,
        Lesson $lesson,
        User $user,
        Request $request
    ): string {
        $this->assertVideoReady($lesson);

        $this->validatePlaybackSession(
            $playbackSessionId,
            $lesson,
            $user,
            $request
        );

        $disk = Storage::disk(
            $lesson->hls_disk
                ?: config('lesson_video.hls_disk')
        );

        $masterPath = $this->videoBasePath(
            $lesson
        ) . '/master.m3u8';

        if (!$disk->exists($masterPath)) {
            throw new RuntimeException(
                'Master playlist was not found.'
            );
        }

        $content = $disk->get($masterPath);

        return $this->rewriteMasterManifest(
            $content,
            $lesson,
            $playbackSessionId
        );
    }

    public function variantManifest(
        string $playbackSessionId,
        string $quality,
        Lesson $lesson,
        User $user,
        Request $request
    ): string {
        $this->assertVideoReady($lesson);

        $this->validatePlaybackSession(
            $playbackSessionId,
            $lesson,
            $user,
            $request
        );

        $this->validateQuality($quality);

        $disk = Storage::disk(
            $lesson->hls_disk
                ?: config('lesson_video.hls_disk')
        );

        $variantPath = $this->videoBasePath(
            $lesson
        ) . "/{$quality}/index.m3u8";

        if (!$disk->exists($variantPath)) {
            throw new RuntimeException(
                'Requested video quality was not found.'
            );
        }

        $content = $disk->get($variantPath);

        return $this->rewriteVariantManifest(
            $content,
            $lesson,
            $playbackSessionId,
            $quality
        );
    }

    /**
     * إنشاء رابط TS قصير الصلاحية لحظة طلب المقطع.
     *
     * @return array<string, string>
     */
    public function createSegmentTicket(
        string $playbackSessionId,
        string $quality,
        string $segment,
        Lesson $lesson,
        User $user,
        Request $request
    ): array {
        $this->validatePlaybackSession(
            $playbackSessionId,
            $lesson,
            $user,
            $request
        );

        $relativePath = $this->segmentRelativePath(
            $lesson,
            $quality,
            $segment
        );

        $disk = Storage::disk(
            $lesson->hls_disk
                ?: config('lesson_video.hls_disk')
        );

        if (!$disk->exists($relativePath)) {
            throw new RuntimeException(
                'Video segment was not found.'
            );
        }

        $expiresAt = now()->addSeconds(
            (int) config(
                'lesson_video.ticket_ttl_seconds',
                20
            )
        );

        $signedUrl =
            $this->url->temporarySignedRoute(
                'user.lessons.video.hls.segment',
                $expiresAt,
                [
                    'lesson' => $lesson->id,
                    'psid' => $playbackSessionId,
                    'quality' => $quality,
                    'segment' => $segment,
                ]
            );

        return [
            'segment_url' => $signedUrl,
            'expires_at' =>
                $expiresAt->toIso8601String(),
        ];
    }

    /**
     * يعيد URI داخلي لـNginx وليس مسار نظام الملفات.
     */
    public function getInternalSegmentUri(
        string $playbackSessionId,
        string $quality,
        string $segment,
        Lesson $lesson,
        Request $request
    ): string {
        /*
         * لا يوجد User هنا لأن الرابط النهائي Signed،
         * لكننا نتحقق من Session وUser-Agent وIP الاختياري.
         */
        $this->validatePlaybackSession(
            $playbackSessionId,
            $lesson,
            null,
            $request
        );

        $relativePath = $this->segmentRelativePath(
            $lesson,
            $quality,
            $segment
        );

        $disk = Storage::disk(
            $lesson->hls_disk
                ?: config('lesson_video.hls_disk')
        );

        if (!$disk->exists($relativePath)) {
            throw new RuntimeException(
                'Video segment was not found.'
            );
        }

        return '/_protected_lesson_hls/' .
            $relativePath;
    }

    /**
     * @return array<string, mixed>
     */
    public function validatePlaybackSession(
        string $playbackSessionId,
        Lesson $lesson,
        ?User $user,
        Request $request
    ): array {
        if (!preg_match(
            '/\A[A-Za-z0-9]{64}\z/',
            $playbackSessionId
        )) {
            throw new AuthorizationException(
                'جلسة التشغيل غير صالحة.'
            );
        }

        $cacheKey = $this->playbackCacheKey(
            $playbackSessionId
        );

        $session = $this->cache->get(
            $cacheKey
        );

        if (!is_array($session)) {
            throw new AuthorizationException(
                'جلسة التشغيل منتهية.'
            );
        }

        if (
            (int) ($session['lesson_id'] ?? 0)
            !==
            (int) $lesson->id
        ) {
            throw new AuthorizationException(
                'غير مصرح بهذا الفيديو.'
            );
        }

        if (
            $user !== null &&
            (int) ($session['user_id'] ?? 0)
            !==
            (int) $user->id
        ) {
            throw new AuthorizationException(
                'جلسة التشغيل لا تخص هذا المستخدم.'
            );
        }

        $absoluteExpiresAt = (int) (
            $session['absolute_expires_at'] ?? 0
        );

        if (
            $absoluteExpiresAt <= 0 ||
            now()->timestamp >= $absoluteExpiresAt
        ) {
            $this->cache->forget($cacheKey);

            throw new AuthorizationException(
                'انتهت المدة القصوى لجلسة التشغيل.'
            );
        }

        if (
            config(
                'lesson_video.bind_user_agent',
                true
            )
        ) {
            $expectedHash = (string) (
                $session['user_agent_hash'] ?? ''
            );

            if (
                $expectedHash === '' ||
                !hash_equals(
                    $expectedHash,
                    $this->userAgentHash($request)
                )
            ) {
                throw new AuthorizationException(
                    'تم رفض جهاز التشغيل.'
                );
            }
        }

        if (
            config(
                'lesson_video.bind_ip',
                false
            )
        ) {
            $expectedHash = (string) (
                $session['ip_hash'] ?? ''
            );

            if (
                $expectedHash === '' ||
                !hash_equals(
                    $expectedHash,
                    $this->ipHash($request)
                )
            ) {
                throw new AuthorizationException(
                    'تغير عنوان شبكة جلسة التشغيل.'
                );
            }
        }

        if (
            config(
                'lesson_video.single_session_per_lesson',
                true
            )
        ) {
            $activePlaybackSessionId =
                $this->cache->get(
                    $this->activeSessionCacheKey(
                        (int) $session['user_id'],
                        $lesson->id
                    )
                );

            if (
                !is_string($activePlaybackSessionId) ||
                !hash_equals(
                    $activePlaybackSessionId,
                    $playbackSessionId
                )
            ) {
                throw new AuthorizationException(
                    'تم فتح الفيديو في جلسة أخرى.'
                );
            }
        }

        /*
         * تمديد Idle Timeout مع عدم تجاوز Absolute Timeout.
         */
        $this->storeSession(
            $playbackSessionId,
            $session
        );

        return $session;
    }

    private function rewriteMasterManifest(
        string $content,
        Lesson $lesson,
        string $playbackSessionId
    ): string {
        $lines = preg_split(
            '/\r\n|\r|\n/',
            trim($content)
        );

        $result = [];

        foreach ($lines ?: [] as $line) {
            $trimmed = trim($line);

            if (
                $trimmed === '' ||
                str_starts_with($trimmed, '#')
            ) {
                $result[] = $line;
                continue;
            }

            /*
             * FFmpeg يكتب مثل:
             * 360p/index.m3u8
             */
            $quality = basename(
                dirname($trimmed)
            );

            $this->validateQuality($quality);

            $result[] = $this->url->route(
                'user.lessons.video.hls.variant',
                [
                    'lesson' => $lesson->id,
                    'psid' => $playbackSessionId,
                    'quality' => $quality,
                ]
            );
        }

        return implode("\n", $result) . "\n";
    }

    private function rewriteVariantManifest(
        string $content,
        Lesson $lesson,
        string $playbackSessionId,
        string $quality
    ): string {
        $lines = preg_split(
            '/\r\n|\r|\n/',
            trim($content)
        );

        $result = [];

        foreach ($lines ?: [] as $line) {
            $trimmed = trim($line);

            if (
                $trimmed === '' ||
                str_starts_with($trimmed, '#')
            ) {
                $result[] = $line;
                continue;
            }

            $segment = basename($trimmed);

            $this->validateSegment($segment);

            /*
             * الـPlaylist لا تحتوي رابط الملف الحقيقي.
             * تحتوي Ticket endpoint فقط.
             */
            $result[] = $this->url->route(
                'user.lessons.video.hls.ticket',
                [
                    'lesson' => $lesson->id,
                    'psid' => $playbackSessionId,
                    'quality' => $quality,
                    'segment' => $segment,
                ]
            );
        }

        return implode("\n", $result) . "\n";
    }

    private function segmentRelativePath(
        Lesson $lesson,
        string $quality,
        string $segment
    ): string {
        $this->validateQuality($quality);
        $this->validateSegment($segment);

        return $this->videoBasePath($lesson) .
            "/{$quality}/{$segment}";
    }

    private function videoBasePath(
        Lesson $lesson
    ): string {
        $path = trim(
            (string) $lesson->hls_path,
            '/'
        );

        if (
            $path === '' ||
            str_contains($path, '..')
        ) {
            throw new RuntimeException(
                'Invalid HLS path.'
            );
        }

        return $path;
    }

    private function validateQuality(
        string $quality
    ): void {
        if (!preg_match(
            '/\A[A-Za-z0-9_-]{1,30}\z/',
            $quality
        )) {
            throw new AuthorizationException(
                'جودة الفيديو غير صالحة.'
            );
        }
    }

    private function validateSegment(
        string $segment
    ): void {
        if (!preg_match(
            '/\Aseg_\d{5}\.ts\z/',
            $segment
        )) {
            throw new AuthorizationException(
                'اسم مقطع الفيديو غير صالح.'
            );
        }
    }

    private function assertVideoReady(
        Lesson $lesson
    ): void {
        if (
            $lesson->hls_status !== 'ready' ||
            !$lesson->hls_path
        ) {
            throw new RuntimeException(
                'الفيديو ما زال قيد المعالجة أو فشلت معالجته.'
            );
        }
    }

    /**
     * @param array<string, mixed> $session
     */
    private function storeSession(
        string $playbackSessionId,
        array $session
    ): void {
        $absoluteExpiresAt =
            Carbon::createFromTimestamp(
                (int) $session[
                    'absolute_expires_at'
                ]
            );

        $idleExpiresAt = now()->addMinutes(
            (int) config(
                'lesson_video.session_idle_minutes',
                20
            )
        );

        $cacheExpiresAt = $idleExpiresAt->lessThan(
            $absoluteExpiresAt
        )
            ? $idleExpiresAt
            : $absoluteExpiresAt;

        $this->cache->put(
            $this->playbackCacheKey(
                $playbackSessionId
            ),
            $session,
            $cacheExpiresAt
        );
    }

    private function userAgentHash(
        Request $request
    ): string {
        return hash_hmac(
            'sha256',
            (string) $request->userAgent(),
            (string) config('app.key')
        );
    }

    private function ipHash(
        Request $request
    ): string {
        return hash_hmac(
            'sha256',
            (string) $request->ip(),
            (string) config('app.key')
        );
    }

    private function playbackCacheKey(
        string $playbackSessionId
    ): string {
        return 'lesson_video:session:' .
            hash('sha256', $playbackSessionId);
    }

    private function activeSessionCacheKey(
        int $userId,
        int $lessonId
    ): string {
        return sprintf(
            'lesson_video:active:user:%d:lesson:%d',
            $userId,
            $lessonId
        );
    }
}
