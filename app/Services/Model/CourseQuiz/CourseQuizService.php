<?php

namespace App\Services\Model\CourseQuiz;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CourseQuiz;
use App\Http\Resources\Model\CourseQuizResource;

class CourseQuizService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CourseQuiz::class
        );

        $this->resource = CourseQuizResource::class;
    }
}