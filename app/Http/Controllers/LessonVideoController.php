<?php

namespace App\Http\Controllers;

use App\Models\CourseApplication;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;


class LessonVideoController extends Controller
{
    private const VIDEO_URL_TTL_SECONDS = 5;
    private const VIDEO_URL_REFRESH_BEFORE_SECONDS = 30;

    public function stream(Request $request, Lesson $lesson)
    {
        $user = $request->user('user');
        if (!$user) {
            abort(401);
        }

        $this->verifyAccess($request, $lesson);

        $playbackSessionId = (string) \Illuminate\Support\Str::uuid();

        cache()->put(
            $this->playbackCacheKey($playbackSessionId),
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'renewals' => 0,
                'created_at' => now()->toIso8601String(),
            ],
            now()->addMinutes(90)
        );

        $expiresAt = now()->addSeconds(self::VIDEO_URL_TTL_SECONDS);

        $videoUrl = URL::temporarySignedRoute(
            'user.api.lessons.video.file',
            $expiresAt,
            [
                'lesson' => $lesson->id,
                'psid' => $playbackSessionId,
            ]
        );

        return response()->json([
            'video_url' => $videoUrl,
            'playback_session_id' => $playbackSessionId,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    public function getVideoFile(Request $request, Lesson $lesson)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'الرابط منتهي الصلاحية. يرجى التحديث.');
        }

        $playbackSessionId = (string) $request->query('psid');
        if ($playbackSessionId === '') {
            abort(403, 'جلسة التشغيل غير صالحة.');
        }

        $cacheKey = $this->playbackCacheKey($playbackSessionId);
        $session = cache()->get($cacheKey);

        if (!$session) {
            abort(403, 'جلسة التشغيل منتهية.');
        }

        if ((int) ($session['lesson_id'] ?? 0) !== (int) $lesson->id) {
            abort(403, 'غير مصرح.');
        }

        $disk = Storage::disk('private');
        $path = $lesson->video_url;

        if (!$disk->exists($path)) {
            abort(404, 'الفيديو غير موجود.');
        }

        $fullPath = $disk->path($path);

        return $this->streamFileWithRange($request, $fullPath);
    }

    protected function streamFileWithRange(Request $request, string $filePath)
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            abort(404, 'الفيديو غير متاح.');
        }

        $size = filesize($filePath);
        if ($size === false || $size <= 0) {
            abort(404, 'الفيديو غير متاح.');
        }

        $mimeType = $this->detectMimeType($filePath);

        $start = 0;
        $end = $size - 1;
        $status = 200;

        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0, private',
            //'X-Content-Type-Options' => 'nosniff',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $rangeHeader = $request->header('Range');

        if ($rangeHeader) {
            $range = $this->parseRangeHeader($rangeHeader, $size);

            if ($range === null) {
                return response('', 416, [
                    'Content-Range' => "bytes */{$size}",
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            [$start, $end] = $range;
            $status = 206;

            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
            $headers['Content-Length'] = $end - $start + 1;
        } else {
            $headers['Content-Length'] = $size;
        }

        return new StreamedResponse(function () use ($filePath, $start, $end) {
            $handle = fopen($filePath, 'rb');

            if ($handle === false) {
                return;
            }

            try {
                if ($start > 0) {
                    fseek($handle, $start);
                }

                $remaining = $end - $start + 1;
                $bufferSize = 1024 * 64;

                while ($remaining > 0 && !feof($handle)) {
                    $chunk = fread($handle, min($bufferSize, $remaining));

                    if ($chunk === false || $chunk === '') {
                        break;
                    }

                    echo $chunk;
                    flush();

                    $remaining -= strlen($chunk);

                    if (connection_aborted()) {
                        break;
                    }
                }
            } finally {
                fclose($handle);
            }
        }, $status, $headers);
    }

    protected function parseRangeHeader(string $rangeHeader, int $size): ?array
    {
        $rangeHeader = trim($rangeHeader);

        if (!preg_match('/^bytes=(\d*)-(\d*)$/', $rangeHeader, $matches)) {
            return null;
        }

        $startRaw = $matches[1];
        $endRaw = $matches[2];

        if ($startRaw === '' && $endRaw !== '') {
            $suffixLength = (int) $endRaw;

            if ($suffixLength <= 0) {
                return null;
            }

            if ($suffixLength >= $size) {
                return [0, $size - 1];
            }

            return [$size - $suffixLength, $size - 1];
        }

        if ($startRaw === '') {
            return null;
        }

        $start = (int) $startRaw;
        $end = $endRaw === '' ? $size - 1 : (int) $endRaw;

        if ($start >= $size) {
            return null;
        }

        if ($end >= $size) {
            $end = $size - 1;
        }

        if ($end < $start) {
            return null;
        }

        return [$start, $end];
    }

    protected function verifyAccess(Request $request, Lesson $lesson)
    {
        $user = $request->user('user');

        if (!$user) {
            abort(401, 'غير مصرح. يرجى تسجيل الدخول من جديد.');
        }

        $lesson->loadMissing('course');

        if (!$lesson->course->is_free && !$lesson->free_preview) {
            $hasAccess = CourseApplication::where('course_id', $lesson->course_id)
                ->where('applicant_id', $user->id)
                ->where('status', 1)
                ->exists();

            if (!$hasAccess) {
                abort(403, 'غير مصرح بالوصول.');
            }
        }
    }

    protected function detectMimeType(string $filePath): string
    {
        $mime = null;

        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($filePath);
        }

        if (!is_string($mime) || $mime === '') {
            $mime = 'video/mp4';
        }

        return $mime;
    }

    public function refresh(Request $request, Lesson $lesson)
    {
        $user = $request->user('user');
        if (!$user) {
            abort(401);
        }

        $request->validate([
            'playback_session_id' => ['required', 'string'],
        ]);

        $cacheKey = $this->playbackCacheKey($request->playback_session_id);
        $session = cache()->get($cacheKey);

        if (!$session) {
            abort(403, 'جلسة التشغيل منتهية.');
        }

        if ((int) $session['user_id'] !== (int) $user->id) {
            abort(403, 'غير مصرح.');
        }

        if ((int) $session['lesson_id'] !== (int) $lesson->id) {
            abort(403, 'غير مصرح.');
        }

        if (($session['renewals'] ?? 0) >= 500) {
            abort(429, 'تم تجاوز حد التجديد.');
        }

        $session['renewals'] = ($session['renewals'] ?? 0) + 1;
        cache()->put($cacheKey, $session, now()->addMinutes(15));

        $expiresAt = now()->addSeconds(self::VIDEO_URL_TTL_SECONDS);

        $videoUrl = URL::temporarySignedRoute(
            'user.api.lessons.video.file',
            $expiresAt,
            [
                'lesson' => $lesson->id,
                'psid' => $request->playback_session_id,
            ]
        );

        return response()->json([
            'video_url' => $videoUrl,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    protected function playbackCacheKey(string $playbackSessionId): string
    {
        return "lesson_playback:{$playbackSessionId}";
    }


    public function show(Request $request, Lesson $lesson)
    {
        $admin = $request->user('admin');

        if (!$admin) {
            abort(401);
        }

        $path = $lesson->video_url;

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('private')->path($path), [
            'Content-Type' => Storage::disk('private')->mimeType($path),
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
        ]);
    }
    public function showForTest(Request $request, Lesson $lesson)
    {
        $user = $request->user('user');

        if (!$user) {
            abort(401);
        }

        $path = $lesson->video_url;

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('private')->path($path), [
            'Content-Type' => Storage::disk('private')->mimeType($path),
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
        ]);
    }

    public function showForInstructor(Request $request, Lesson $lesson)
    {
        $instructor = $request->user('instructor');

        if (!$instructor) {
            abort(401);
        }

        $lesson->loadMissing('course');

        if (
            !$lesson->course ||
            (int) $lesson->course->created_by !== (int) $instructor->id
        ) {
            abort(403, 'You are not allowed to access this lesson video.');
        }

        $path = $lesson->video_url;

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('private')->path($path), [
            'Content-Type' => Storage::disk('private')->mimeType($path),
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
        ]);
    }

}
