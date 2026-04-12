<?php

namespace App\Services\Model\CourseQuizAttemptAnswer;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CourseQuizAttemptAnswer;
use App\Http\Resources\Model\CourseQuizAttemptAnswerResource;

class CourseQuizAttemptAnswerService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CourseQuizAttemptAnswer::class
        );

        $this->resource = CourseQuizAttemptAnswerResource::class;
    }
}