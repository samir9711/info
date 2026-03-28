<?php

namespace App\Services\Model\QuizAttemptAnswer;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\QuizAttemptAnswer;
use App\Http\Resources\Model\QuizAttemptAnswerResource;

class QuizAttemptAnswerService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = QuizAttemptAnswer::class
        );

        $this->resource = QuizAttemptAnswerResource::class;
    }
}