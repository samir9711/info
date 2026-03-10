<?php

namespace App\Services\Model\Quiz;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Quiz;
use App\Http\Resources\Model\QuizResource;

class QuizService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Quiz::class
        );

        $this->resource = QuizResource::class;
    }
}