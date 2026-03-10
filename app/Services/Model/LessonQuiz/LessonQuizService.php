<?php

namespace App\Services\Model\LessonQuiz;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\LessonQuiz;
use App\Http\Resources\Model\LessonQuizResource;

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
}