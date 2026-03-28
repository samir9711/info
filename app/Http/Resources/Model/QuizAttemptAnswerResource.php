<?php

namespace App\Http\Resources\Model;

use App\Models\QuizAttemptAnswer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class QuizAttemptAnswerResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        return $this->initResource(
            ModelColumnsService::getServiceFor(
                QuizAttemptAnswer::class
            )
        );
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}