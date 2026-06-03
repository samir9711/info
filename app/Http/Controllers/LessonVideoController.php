<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonVideo\RefreshLessonVideoRequest;
use App\Http\Requests\LessonVideo\StreamLessonVideoRequest;
use App\Models\Lesson;
use App\Policies\LessonPolicy;
use App\Services\LessonVideo\LessonVideoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class LessonVideoController extends Controller
{
    public function __construct(
        private readonly LessonVideoService $lessonVideoService,
    ) {
    }

    public function stream(StreamLessonVideoRequest $request, Lesson $lesson): JsonResponse
    {
        $user = $request->user('user');

        // Authorize the user to stream the video for this lesson
        $this->authorize('streamVideo', $lesson);

        $streamData = $this->lessonVideoService->createStream($lesson, $user);

        return response()->json($streamData);
    }

    public function getVideoFile(Request $request, Lesson $lesson): StreamedResponse|JsonResponse
    {
        $user = $request->user('user');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح. يرجى تسجيل الدخول من جديد.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$request->hasValidSignature()) {
            return response()->json([
                'success' => false,
                'message' => 'الرابط منتهي الصلاحية. يرجى التحديث.',
            ], Response::HTTP_FORBIDDEN);
        }

        $playbackSessionId = (string) $request->query('psid');
        if ($playbackSessionId === '') {
            return response()->json([
                'success' => false,
                'message' => 'جلسة التشغيل غير صالحة.',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $this->authorize('getVideoFile', [$lesson, $playbackSessionId]);
            $this->lessonVideoService->validatePlaybackSession($playbackSessionId, $lesson, $user);
        } catch (AuthorizationException $e) {
            logger()->warning('Lesson video denied', [
                'lesson_id' => $lesson->id,
                'user_id' => $user->id,
                'psid' => $playbackSessionId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }

        $disk = Storage::disk('private');
        $path = $lesson->video_url;

        if (!$path || !$disk->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'الفيديو غير موجود.',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->streamFileWithRange($request, $disk->path($path));
    }

    public function refresh(RefreshLessonVideoRequest $request, Lesson $lesson): JsonResponse
    {
        $user = $request->user('user');

        // Authorize the user to refresh the video playback for this lesson
        $this->authorize('refreshVideo', [$lesson,$request->playback_session_id]);

        try {
            $refreshData = $this->lessonVideoService->refreshStream(
                $request->playback_session_id,
                $lesson,
                $user
            );

            return response()->json($refreshData);
        } catch (\DomainException $e) {
            // Handle the renewal limit exception
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }
    }

    protected function streamFileWithRange(
    Request $request,
    string $filePath
): StreamedResponse|JsonResponse|Response
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'الفيديو غير متاح.',
            ], Response::HTTP_NOT_FOUND);
        }

        $size = filesize($filePath);
        if ($size === false || $size <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'الفيديو غير متاح.',
            ], Response::HTTP_NOT_FOUND);
        }

        $mimeType = $this->detectMimeType($filePath);

        $start = 0;
        $end = $size - 1;
        $status = 200;

        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0, private',
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

    // The following methods are kept for admin/instructor/test access but we should also refactor them to use policy and service?
    // However, the task is to refactor the main streaming methods. We'll leave them as is for now, but note they bypass the new security.
    // Ideally, we would refactor them too, but the task focuses on the user-facing streaming.

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
