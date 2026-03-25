<?php

namespace App\Http\Controllers\LessonView;

use App\Facades\Services\LessonView\LessonViewFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreLessonViewRequest;
use Illuminate\Http\Request;

class LessonViewController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "lesson_view";
        $this->service = LessonViewFacade::class;
        $this->createRequest = StoreLessonViewRequest::class;
        $this->updateRequest = StoreLessonViewRequest::class;
    }
    public function record(Request $request)
    {
        try {
            $req = app($this->createRequest);
            $user = $request->user();

            $result = $this->service::recordView($req, $user);

            return $this->apiResponse([$this->key => $result], true, null, 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function showForUser(Request $request)
    {
        try {
            $request->validate([
                'lesson_id' => ['required','integer','exists:lessons,id'],
            ]);

            $user = $request->user();
            $lessonId = (int) $request->input('lesson_id');

            $res = $this->service::getForUserLesson($user->id, $lessonId);

            return $this->apiResponse([$this->key => $res]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * POST /v1/lesson-views/stats
     * Body: { "lesson_id": 5 }
     */
    public function stats(Request $request)
    {
        try {
            $request->validate([
                'lesson_id' => ['required','integer','exists:lessons,id'],
            ]);

            $lessonId = (int) $request->input('lesson_id');
            $res = $this->service::lessonStats($lessonId);

            return $this->apiResponse(['stats' => $res]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * POST /v1/courses/progress
     * Body: { "course_id": 3 }
     */
    public function userCourseProgress(Request $request)
    {
        try {
            $request->validate([
                'course_id' => ['required','integer','exists:courses,id'],
            ]);

            $user = $request->user();
            $courseId = (int) $request->input('course_id');

            $res = $this->service::userCourseProgress($user->id, $courseId);

            return $this->apiResponse(['progress' => $res]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function reset(Request $request)
    {
        try {
            $userId = (int) $request->input('user_id');
            $lessonId = (int) $request->input('lesson_id');
            $ok = $this->service::resetView($userId, $lessonId);
            return $this->apiResponse(['message' => $ok ? 'reset done' : 'no record found']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
