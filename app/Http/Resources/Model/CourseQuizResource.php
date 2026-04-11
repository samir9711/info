<?php

namespace App\Http\Resources\Model;

use App\Models\CourseQuiz;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class CourseQuizResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $courseQuiz = $this->resource;


        $courseQuiz->loadMissing([
            'course',
            'quiz.questions.answers',
        ]);

        $data = $this->initResource(
            ModelColumnsService::getServiceFor(CourseQuiz::class)
        );

        $data['course'] = $courseQuiz->relationLoaded('course')
            ? new CourseResource($courseQuiz->course)
            : null;

        $data['quiz'] = $courseQuiz->relationLoaded('quiz') ? [
            'id' => $courseQuiz->quiz->id,
            'title' => $courseQuiz->quiz->title,
            'description' => $courseQuiz->quiz->description,
            'questions' => $courseQuiz->quiz->questions->map(function ($question) use ($request) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'image' => $question->image,
                    'answers' => $question->answers->map(function ($answer) use ($request) {
                        $item = [
                            'id' => $answer->id,
                            'answer' => $answer->answer,
                            'is_correct' => $answer->is_correct,
                        ];



                        return $item;
                    })->values(),
                ];
            })->values(),
        ] : null;

        return $data;
    }

}
