<?php

namespace App\Http\Resources\Model;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCourseQuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'quiz_id' => $this->quiz_id,
            'is_final' => $this->is_final,
            'quiz' => [
                'id' => $this->quiz?->id,
                'title' => $this->quiz?->title,
                'description' => $this->quiz?->description,
                'questions' => PublicCourseQuizQuestionResource::collection(
                    $this->quiz?->questions ?? collect()
                ),
            ],
        ];
    }
}
