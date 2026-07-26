<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonVideo\RefreshLessonVideoRequest;
use App\Http\Requests\LessonVideo\StreamLessonVideoRequest;
use App\Models\Lesson;
use App\Services\LessonVideo\LessonVideoService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
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

    /**
     * إنشاء جلسة تشغيل جديدة.
     *
     * يدعم:
     * - User
     * - Admin
     * - Instructor
     */
    public function stream(
        StreamLessonVideoRequest $request,
        Lesson $lesson
    ): JsonResponse {
        $actor = $this->authenticatedActor(
            $request
        );

        Gate::forUser($actor)->authorize(
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

            'data' => $this->service
                ->createSession(
                    $lesson,
                    $actor,
                    $request
                ),
        ]);
    }

    /**
     * تجديد جلسة التشغيل الحالية.
     */
    public function refresh(
        RefreshLessonVideoRequest $request,
        Lesson $lesson
    ): JsonResponse {
        $actor = $this->authenticatedActor(
            $request
        );

        Gate::forUser($actor)->authorize(
            'streamVideo',
            $lesson
        );

        return response()->json([
            'status' => true,

            'data' => $this->service
                ->refreshSession(
                    $request->validated(
                        'playback_session_id'
                    ),
                    $lesson,
                    $actor,
                    $request
                ),
        ]);
    }

    /**
     * إرجاع Master Playlist.
     */
    public function master(
        Request $request,
        Lesson $lesson,
        string $psid
    ): Response {
        $actor = $this->authenticatedActor(
            $request
        );

        Gate::forUser($actor)->authorize(
            'streamVideo',
            $lesson
        );

        $manifest = $this->service
            ->masterManifest(
                $psid,
                $lesson,
                $actor,
                $request
            );

        return $this->manifestResponse(
            $manifest
        );
    }

    /**
     * إرجاع Playlist الخاصة بجودة معينة.
     */
    public function variant(
        Request $request,
        Lesson $lesson,
        string $psid,
        string $quality
    ): Response {
        $actor = $this->authenticatedActor(
            $request
        );

        Gate::forUser($actor)->authorize(
            'streamVideo',
            $lesson
        );

        $manifest = $this->service
            ->variantManifest(
                $psid,
                $quality,
                $lesson,
                $actor,
                $request
            );

        return $this->manifestResponse(
            $manifest
        );
    }

    /**
     * إصدار رابط Signed مؤقت لمقطع TS.
     */
    public function ticket(
        Request $request,
        Lesson $lesson,
        string $psid,
        string $quality,
        string $segment
    ): JsonResponse {
        $actor = $this->authenticatedActor(
            $request
        );

        /*
         * نتحقق من Policy أيضًا حتى يتم منع الوصول
         * إذا تم سحب صلاحية الحساب أثناء المشاهدة.
         */
        Gate::forUser($actor)->authorize(
            'streamVideo',
            $lesson
        );

        $ticket = $this->service
            ->createSegmentTicket(
                $psid,
                $quality,
                $segment,
                $lesson,
                $actor,
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

    /**
     * إرسال ملف TS عن طريق Nginx.
     *
     * هذا الراوت لا يحتاج Bearer Token.
     * الحماية تتم عن طريق:
     * - Signed URL
     * - صلاحية قصيرة
     * - Playback Session
     */
    public function segment(
        Request $request,
        Lesson $lesson,
        string $psid,
        string $quality,
        string $segment
    ): Response {
        $internalUri = $this->service
            ->getInternalSegmentUri(
                $psid,
                $quality,
                $segment,
                $lesson,
                $request
            );

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

    /**
     * جلب الحساب المسجل من الـGuard الذي نجح.
     *
     * Middleware:
     * auth:user,admin,instructor
     *
     * يقوم Laravel بتعيين الـGuard الناجح كـGuard افتراضي
     * ولذلك يمكن استخدام $request->user().
     *
     * @throws AuthenticationException
     */
    private function authenticatedActor(
        Request $request
    ): Authenticatable {
        $actor = $request->user();

        if (!$actor instanceof Authenticatable) {
            throw new AuthenticationException(
                'Unauthenticated.'
            );
        }

        return $actor;
    }

    /**
     * إرجاع استجابة Playlist.
     */
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
