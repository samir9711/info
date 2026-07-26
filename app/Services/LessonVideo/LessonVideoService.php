<?php

namespace App\Services\LessonVideo;

use App\Models\Lesson;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
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
     * إنشاء جلسة تشغيل جديدة.
     *
     * @return array<string, mixed>
     */
    public function createSession(
        Lesson $lesson,
        Authenticatable $actor,
        Request $request
    ): array {
        $this->assertVideoReady($lesson);

        $playbackSessionId = Str::random(64);

        $absoluteExpiresAt = now()->addMinutes(
            (int) config(
                'lesson_video.session_max_minutes',
                120
            )
        );

        [
            $actorType,
            $actorId,
        ] = $this->actorIdentity($actor);

        $session = [
            /*
             * نخزن نوع الحساب والرقم معًا.
             *
             * هذا يمنع تداخل:
             * User ID 5
             * Admin ID 5
             * Instructor ID 5
             */
            'actor_type' => $actorType,
            'actor_id' => $actorId,

            'lesson_id' => (int) $lesson->id,

            'created_at' => now()->timestamp,

            'absolute_expires_at' =>
                $absoluteExpiresAt->timestamp,

            /*
             * تبقى القيم محفوظة حتى عند تعطيل التحقق منها.
             */
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
            $activeKey =
                $this->activeSessionCacheKey(
                    $actorType,
                    $actorId,
                    $lesson->id
                );

            $oldPlaybackSessionId =
                $this->cache->get($activeKey);

            /*
             * إنشاء جلسة جديدة يبطل الجلسة القديمة
             * لنفس الحساب ونفس الدرس.
             */
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
     * تجديد Idle Timeout للجلسة الحالية.
     *
     * لا يتم تمديد Absolute Expiration.
     *
     * @return array<string, mixed>
     */
    public function refreshSession(
        string $playbackSessionId,
        Lesson $lesson,
        Authenticatable $actor,
        Request $request
    ): array {
        $session =
            $this->validatePlaybackSession(
                $playbackSessionId,
                $lesson,
                $actor,
                $request
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
                Carbon::createFromTimestamp(
                    (int) $session[
                        'absolute_expires_at'
                    ]
                )->toIso8601String(),
        ];
    }

    /**
     * إرجاع Master Playlist بعد استبدال مسارات
     * الجودات بروابط Laravel المحمية.
     */
    public function masterManifest(
        string $playbackSessionId,
        Lesson $lesson,
        Authenticatable $actor,
        Request $request
    ): string {
        $this->assertVideoReady($lesson);

        $this->validatePlaybackSession(
            $playbackSessionId,
            $lesson,
            $actor,
            $request
        );

        $disk = Storage::disk(
            $lesson->hls_disk
                ?: config('lesson_video.hls_disk')
        );

        $masterPath =
            $this->videoBasePath($lesson) .
            '/master.m3u8';

        if (!$disk->exists($masterPath)) {
            throw new RuntimeException(
                'Master playlist was not found.'
            );
        }

        $content = $disk->get(
            $masterPath
        );

        return $this->rewriteMasterManifest(
            $content,
            $lesson,
            $playbackSessionId
        );
    }

    /**
     * إرجاع Playlist الخاصة بجودة معينة.
     */
    public function variantManifest(
        string $playbackSessionId,
        string $quality,
        Lesson $lesson,
        Authenticatable $actor,
        Request $request
    ): string {
        $this->assertVideoReady($lesson);

        $this->validatePlaybackSession(
            $playbackSessionId,
            $lesson,
            $actor,
            $request
        );

        $this->validateQuality(
            $quality
        );

        $disk = Storage::disk(
            $lesson->hls_disk
                ?: config('lesson_video.hls_disk')
        );

        $variantPath =
            $this->videoBasePath($lesson) .
            "/{$quality}/index.m3u8";

        if (!$disk->exists($variantPath)) {
            throw new RuntimeException(
                'Requested video quality was not found.'
            );
        }

        $content = $disk->get(
            $variantPath
        );

        return $this->rewriteVariantManifest(
            $content,
            $lesson,
            $playbackSessionId,
            $quality
        );
    }

    /**
     * إنشاء رابط TS قصير الصلاحية.
     *
     * @return array<string, string>
     */
    public function createSegmentTicket(
        string $playbackSessionId,
        string $quality,
        string $segment,
        Lesson $lesson,
        Authenticatable $actor,
        Request $request
    ): array {
        $this->validatePlaybackSession(
            $playbackSessionId,
            $lesson,
            $actor,
            $request
        );

        $relativePath =
            $this->segmentRelativePath(
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
     * إرجاع URI داخلي إلى Nginx.
     *
     * هذا الطلب لا يحتوي Actor لأنه يعمل بواسطة
     * Signed URL، لذلك نتحقق من Session نفسها.
     */
    public function getInternalSegmentUri(
        string $playbackSessionId,
        string $quality,
        string $segment,
        Lesson $lesson,
        Request $request
    ): string {
        $this->validatePlaybackSession(
            $playbackSessionId,
            $lesson,
            null,
            $request
        );

        $relativePath =
            $this->segmentRelativePath(
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
     * التحقق من جلسة التشغيل.
     *
     * @return array<string, mixed>
     */
    public function validatePlaybackSession(
        string $playbackSessionId,
        Lesson $lesson,
        ?Authenticatable $actor,
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

        $cacheKey =
            $this->playbackCacheKey(
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

        /*
         * التأكد أن الجلسة مرتبطة بنفس الدرس.
         */
        if (
            (int) (
                $session['lesson_id'] ?? 0
            ) !==
            (int) $lesson->id
        ) {
            throw new AuthorizationException(
                'غير مصرح بهذا الفيديو.'
            );
        }

        /*
         * عند وجود Actor، نتحقق من نوع الحساب ورقمه.
         */
        if ($actor !== null) {
            [
                $currentActorType,
                $currentActorId,
            ] = $this->actorIdentity($actor);

            $sessionActorType = (string) (
                $session['actor_type'] ?? ''
            );

            $sessionActorId = (string) (
                $session['actor_id'] ?? ''
            );

            if (
                $sessionActorType === '' ||
                $sessionActorId === '' ||
                !hash_equals(
                    $sessionActorType,
                    $currentActorType
                ) ||
                !hash_equals(
                    $sessionActorId,
                    $currentActorId
                )
            ) {
                throw new AuthorizationException(
                    'جلسة التشغيل لا تخص هذا الحساب.'
                );
            }
        }

        $absoluteExpiresAt = (int) (
            $session[
                'absolute_expires_at'
            ] ?? 0
        );

        if (
            $absoluteExpiresAt <= 0 ||
            now()->timestamp >=
                $absoluteExpiresAt
        ) {
            $this->cache->forget(
                $cacheKey
            );

            throw new AuthorizationException(
                'انتهت المدة القصوى لجلسة التشغيل.'
            );
        }

        /*
         * معطل حاليًا في .env بسبب مرور الطلب
         * عبر Next.js Proxy.
         */
        if (
            config(
                'lesson_video.bind_user_agent',
                false
            )
        ) {
            $expectedHash = (string) (
                $session[
                    'user_agent_hash'
                ] ?? ''
            );

            if (
                $expectedHash === '' ||
                !hash_equals(
                    $expectedHash,
                    $this->userAgentHash(
                        $request
                    )
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

        /*
         * التحقق أن الجلسة هي الجلسة النشطة الحالية
         * لهذا الحساب وهذا الدرس.
         */
        if (
            config(
                'lesson_video.single_session_per_lesson',
                true
            )
        ) {
            $sessionActorType = (string) (
                $session['actor_type'] ?? ''
            );

            $sessionActorId = (string) (
                $session['actor_id'] ?? ''
            );

            if (
                $sessionActorType === '' ||
                $sessionActorId === ''
            ) {
                throw new AuthorizationException(
                    'بيانات جلسة التشغيل غير صالحة.'
                );
            }

            $activeKey =
                $this->activeSessionCacheKey(
                    $sessionActorType,
                    $sessionActorId,
                    $lesson->id
                );

            $activePlaybackSessionId =
                $this->cache->get(
                    $activeKey
                );

            if (
                !is_string(
                    $activePlaybackSessionId
                ) ||
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
         * تمديد Idle Timeout دون تجاوز
         * Absolute Timeout.
         */
        $this->storeSession(
            $playbackSessionId,
            $session
        );

        return $session;
    }

    /**
     * استبدال روابط الجودات في Master Playlist.
     */
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
                str_starts_with(
                    $trimmed,
                    '#'
                )
            ) {
                $result[] = $line;
                continue;
            }

            /*
             * FFmpeg يكتب:
             * 360p/index.m3u8
             */
            $quality = basename(
                dirname($trimmed)
            );

            $this->validateQuality(
                $quality
            );

            $result[] = $this->url->route(
                'user.lessons.video.hls.variant',
                [
                    'lesson' => $lesson->id,
                    'psid' =>
                        $playbackSessionId,
                    'quality' => $quality,
                ]
            );
        }

        return implode(
            "\n",
            $result
        ) . "\n";
    }

    /**
     * استبدال أسماء ملفات TS بروابط Ticket.
     */
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
                str_starts_with(
                    $trimmed,
                    '#'
                )
            ) {
                $result[] = $line;
                continue;
            }

            $segment = basename(
                $trimmed
            );

            $this->validateSegment(
                $segment
            );

            $result[] = $this->url->route(
                'user.lessons.video.hls.ticket',
                [
                    'lesson' => $lesson->id,
                    'psid' =>
                        $playbackSessionId,
                    'quality' => $quality,
                    'segment' => $segment,
                ]
            );
        }

        return implode(
            "\n",
            $result
        ) . "\n";
    }

    /**
     * بناء مسار مقطع TS النسبي.
     */
    private function segmentRelativePath(
        Lesson $lesson,
        string $quality,
        string $segment
    ): string {
        $this->validateQuality(
            $quality
        );

        $this->validateSegment(
            $segment
        );

        return $this->videoBasePath(
            $lesson
        ) . "/{$quality}/{$segment}";
    }

    /**
     * جلب المسار الأساسي لفيديو HLS.
     */
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

    /**
     * التحقق من اسم الجودة.
     */
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

    /**
     * التحقق من اسم ملف Segment.
     */
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

    /**
     * التحقق أن الفيديو جاهز.
     */
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
     * تخزين جلسة التشغيل مع Idle Timeout.
     *
     * @param array<string, mixed> $session
     */
    private function storeSession(
        string $playbackSessionId,
        array $session
    ): void {
        $absoluteTimestamp = (int) (
            $session[
                'absolute_expires_at'
            ] ?? 0
        );

        if ($absoluteTimestamp <= 0) {
            throw new RuntimeException(
                'Invalid playback expiration.'
            );
        }

        $absoluteExpiresAt =
            Carbon::createFromTimestamp(
                $absoluteTimestamp
            );

        $idleExpiresAt = now()->addMinutes(
            (int) config(
                'lesson_video.session_idle_minutes',
                20
            )
        );

        $cacheExpiresAt =
            $idleExpiresAt->lessThan(
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

    /**
     * استخراج نوع الحساب ورقمه.
     *
     * @return array{0: string, 1: string}
     */
    private function actorIdentity(
        Authenticatable $actor
    ): array {
        $actorType = get_class($actor);

        $actorId = $actor
            ->getAuthIdentifier();

        if (
            $actorType === '' ||
            $actorId === null ||
            $actorId === ''
        ) {
            throw new AuthorizationException(
                'بيانات الحساب غير صالحة.'
            );
        }

        return [
            $actorType,
            (string) $actorId,
        ];
    }

    /**
     * بصمة User-Agent.
     */
    private function userAgentHash(
        Request $request
    ): string {
        return hash_hmac(
            'sha256',
            (string) $request->userAgent(),
            (string) config('app.key')
        );
    }

    /**
     * بصمة IP.
     */
    private function ipHash(
        Request $request
    ): string {
        return hash_hmac(
            'sha256',
            (string) $request->ip(),
            (string) config('app.key')
        );
    }

    /**
     * مفتاح جلسة التشغيل.
     */
    private function playbackCacheKey(
        string $playbackSessionId
    ): string {
        return 'lesson_video:session:' .
            hash(
                'sha256',
                $playbackSessionId
            );
    }

    /**
     * مفتاح الجلسة النشطة للحساب والدرس.
     */
    private function activeSessionCacheKey(
        string $actorType,
        string $actorId,
        int $lessonId
    ): string {
        $actorHash = hash(
            'sha256',
            $actorType . '|' . $actorId
        );

        return sprintf(
            'lesson_video:active:%s:lesson:%d',
            $actorHash,
            $lessonId
        );
    }
}
