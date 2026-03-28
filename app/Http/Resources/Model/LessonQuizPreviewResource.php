<?php

namespace App\Http\Resources\Model;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonQuizPreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'required' => $this->required,
            'quiz' => [
                'id' => $this->quiz?->id,
                'title' => $this->quiz?->title,
                'description' => $this->quiz?->description,
                'questions' => QuizQuestionPreviewResource::collection(
                    $this->whenLoaded('quiz', fn () => $this->quiz->questions)
                ),
            ],
        ];
    }
}
