<?php

namespace App\Services\LessonVideo;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LessonVideoService
{
    private const VIDEO_URL_TTL_SECONDS = 5;
    private const VIDEO_URL_REFRESH_BEFORE_SECONDS = 30;

    public function __construct(
        private readonly Repository $cache,
        private readonly UrlGenerator $url
    ) {
    }

    /**
     * Create a new video streaming session for the given lesson and user.
     *
     * @param  \App\Models\Lesson  $lesson
     * @param  \App\Models\User  $user
     * @return array<string, string>
     */
    public function createStream(Lesson $lesson, User $user): array
    {
        $playbackSessionId = (string) Str::uuid();

        $this->cache->put(
            $this->playbackCacheKey($playbackSessionId),
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'renewals' => 0,
                'created_at' => Carbon::now()->toIso8601String(),
            ],
            Carbon::now()->addMinutes(90)
        );

        $expiresAt = Carbon::now()->addSeconds(self::VIDEO_URL_TTL_SECONDS);

        $videoUrl = $this->url->temporarySignedRoute(
            'user.api.lessons.video.file',
            $expiresAt,
            [
                'lesson' => $lesson->id,
                'psid' => $playbackSessionId,
            ]
        );

        return [
            'video_url' => $videoUrl,
            'playback_session_id' => $playbackSessionId,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /**
     * Refresh an existing video streaming session.
     *
     * @param  string  $playbackSessionId
     * @param  \App\Models\Lesson  $lesson
     * @param  \App\Models\User  $user
     * @return array<string, string>
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function refreshStream(string $playbackSessionId, Lesson $lesson, User $user): array
    {
        $this->validatePlaybackSession($playbackSessionId, $lesson, $user);

        $cacheKey = $this->playbackCacheKey($playbackSessionId);
        $session = $this->cache->get($cacheKey);

        if (($session['renewals'] ?? 0) >= 500) {
            // We'll throw an exception that the controller can catch and turn into a 429 response.
            // We'll create a custom exception? For now, we'll throw an authorization exception with a message.
            // But note: the controller expects to return a 429. We'll let the controller handle the threshold.
            // We'll just return the session and let the controller check? Or we can throw a specific exception.
            // We'll throw an authorization exception with a custom message and let the controller map it to 429.
            // However, the policy doesn't cover this. We'll handle it in the service by returning null? Or we throw.
            // We'll throw an \Illuminate\Auth\Access\AuthorizationException with a message and let the controller catch and return 429.
            // But note: the service should not know about HTTP status codes. We'll throw a domain exception? Let's create a custom exception.
            // For simplicity, we'll throw an authorization exception and the controller will check the message? Not ideal.
            // We'll change: we'll return the session and let the controller check the renewals? But then we break encapsulation.
            // We'll create a custom exception for this.
            throw new \DomainException('تم تجاوز حد التجديد.');
        }

        $session['renewals'] = ($session['renewals'] ?? 0) + 1;
        $this->cache->put($cacheKey, $session, Carbon::now()->addMinutes(15));

        $expiresAt = Carbon::now()->addSeconds(self::VIDEO_URL_TTL_SECONDS);

        $videoUrl = $this->url->temporarySignedRoute(
            'user.api.lessons.video.file',
            $expiresAt,
            [
                'lesson' => $lesson->id,
                'psid' => $playbackSessionId,
            ]
        );

        return [
            'video_url' => $videoUrl,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /**
     * Validate that the playback session belongs to the user and lesson.
     *
     * @param  string  $playbackSessionId
     * @param  \App\Models\Lesson  $lesson
     * @param  \App\Models\User  $user
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function validatePlaybackSession(string $playbackSessionId, Lesson $lesson, User $user): void
    {
        $cacheKey = $this->playbackCacheKey($playbackSessionId);
        $session = $this->cache->get($cacheKey);

        if (!$session) {
            throw new \Illuminate\Auth\Access\AuthorizationException('جلسة التشغيل منتهية.');
        }

        if ((int) ($session['lesson_id'] ?? 0) !== (int) $lesson->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException('غير مصرح.');
        }

        if ((int) $session['user_id'] !== (int) $user->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException('غير مصرح.');
        }
    }

    /**
     * Get the cache key for a playback session ID.
     *
     * @param  string  $playbackSessionId
     * @return string
     */
    protected function playbackCacheKey(string $playbackSessionId): string
    {
        return "lesson_playback:{$playbackSessionId}";
    }
}