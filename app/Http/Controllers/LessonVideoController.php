<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonVideo\RefreshLessonVideoRequest;
use App\Http\Requests\LessonVideo\StreamLessonVideoRequest;
use App\Models\Lesson;
use App\Models\User;
use App\Services\LessonVideo\LessonVideoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class LessonVideoController extends Controller
{
    public function __construct(
        private readonly LessonVideoService $service
    ) {
    }

    public function stream(
        StreamLessonVideoRequest $request,
        Lesson $lesson
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('user');

        Gate::forUser($user)->authorize(
            'streamVideo',
            $lesson
        );

        if ($lesson->hls_status !== 'ready') {
            return response()->json([
                'status' => false,
                'message' => match (
                    $lesson->hls_status
                ) {
                    'processing', 'pending' =>
                        'الفيديو ما زال قيد المعالجة.',

                    'failed' =>
                        'فشلت معالجة الفيديو.',

                    default =>
                        'الفيديو غير جاهز للتشغيل.',
                },
            ], 409);
        }

        return response()->json([
            'status' => true,
            'data' => $this->service->createSession(
                $lesson,
                $user,
                $request
            ),
        ]);
    }

    public function refresh(
        RefreshLessonVideoRequest $request,
        Lesson $lesson
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('user');

        Gate::forUser($user)->authorize(
            'streamVideo',
            $lesson
        );

        return response()->json([
            'status' => true,
            'data' => $this->service->refreshSession(
                $request->validated(
                    'playback_session_id'
                ),
                $lesson,
                $user,
                $request
            ),
        ]);
    }

    public function master(
        Request $request,
        Lesson $lesson,
        string $psid
    ): Response {
        /** @var User $user */
        $user = $request->user('user');

        Gate::forUser($user)->authorize(
            'streamVideo',
            $lesson
        );

        $manifest =
            $this->service->masterManifest(
                $psid,
                $lesson,
                $user,
                $request
            );

        return $this->manifestResponse(
            $manifest
        );
    }

    public function variant(
        Request $request,
        Lesson $lesson,
        string $psid,
        string $quality
    ): Response {
        /** @var User $user */
        $user = $request->user('user');

        Gate::forUser($user)->authorize(
            'streamVideo',
            $lesson
        );

        $manifest =
            $this->service->variantManifest(
                $psid,
                $quality,
                $lesson,
                $user,
                $request
            );

        return $this->manifestResponse(
            $manifest
        );
    }

    public function ticket(
        Request $request,
        Lesson $lesson,
        string $psid,
        string $quality,
        string $segment
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('user');

        /*
         * لا نكرر استعلام CourseApplication هنا؛
         * الجلسة أُنشئت بعد Policy check.
         */
        $ticket =
            $this->service->createSegmentTicket(
                $psid,
                $quality,
                $segment,
                $lesson,
                $user,
                $request
            );

        return response()->json([
            'status' => true,
            'data' => $ticket,
        ])->withHeaders([
            'Cache-Control' =>
                'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    public function segment(
        Request $request,
        Lesson $lesson,
        string $psid,
        string $quality,
        string $segment
    ): Response {
        /*
         * Middleware signed يتحقق من انتهاء الرابط
         * قبل الوصول إلى هنا.
         */
        $internalUri =
            $this->service->getInternalSegmentUri(
                $psid,
                $quality,
                $segment,
                $lesson,
                $request
            );

        /*
         * Laravel لا يقرأ ملف TS.
         * Nginx يرسله مباشرة.
         */
        return response('', 200, [
            'Content-Type' => 'video/mp2t',

            'X-Accel-Redirect' =>
                $internalUri,

            'Cache-Control' =>
                'private, no-store, no-cache, ' .
                'must-revalidate, max-age=0',

            'Pragma' => 'no-cache',

            'X-Content-Type-Options' =>
                'nosniff',
        ]);
    }

    private function manifestResponse(
        string $content
    ): Response {
        return response($content, 200, [
            'Content-Type' =>
                'application/vnd.apple.mpegurl',

            'Cache-Control' =>
                'private, no-store, no-cache, ' .
                'must-revalidate, max-age=0',

            'Pragma' => 'no-cache',

            'X-Content-Type-Options' =>
                'nosniff',
        ]);
    }
}
