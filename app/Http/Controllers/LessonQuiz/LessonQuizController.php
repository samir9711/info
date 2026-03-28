<?php

namespace App\Http\Controllers\LessonQuiz;

use App\Facades\Services\LessonQuiz\LessonQuizFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreLessonQuizRequest;
use App\Http\Requests\Model\LessonQuizPreviewRequest;
use App\Services\Model\LessonQuiz\LessonQuizService;
use Illuminate\Http\Request;

class LessonQuizController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "lesson_quiz";
        $this->service = LessonQuizFacade::class;
        $this->createRequest = StoreLessonQuizRequest::class;
        $this->updateRequest = StoreLessonQuizRequest::class;
    }

    public function preview(LessonQuizPreviewRequest $request, LessonQuizService $service)
    {
        $data = $request->validated();

        return response()->json([
            'data' => [
                'lesson_id' => (int) $data['lesson_id'],
                'quizzes' => $service->preview((int) $data['lesson_id']),
            ],
            'status' => true,
            'error' => null,
            'statusCode' => 200,
        ]);
    }

}
