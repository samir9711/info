<?php

namespace App\Services\Model\LessonQuiz;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\LessonQuiz;
use App\Http\Resources\Model\LessonQuizResource;
use App\Http\Resources\Model\LessonQuizPreviewResource;

class LessonQuizService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = LessonQuiz::class
        );

        $this->resource = LessonQuizResource::class;
    }
    public function preview(int $lessonId): array
    {
        $lessonQuizzes = LessonQuiz::query()
            ->where('lesson_id', $lessonId)
            ->with([
                'quiz.questions.answers' => function ($q) {
                    $q->orderBy('id');
                },
                'quiz',
            ])
            ->get();

        return LessonQuizPreviewResource::collection($lessonQuizzes)->resolve();
    }
}
