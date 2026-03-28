<?php

namespace App\Http\Resources\Model;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionPreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'image' => $this->image,
            'answers' => QuizAnswerPreviewResource::collection($this->whenLoaded('answers')),
        ];
    }
}
