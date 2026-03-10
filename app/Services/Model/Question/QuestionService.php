<?php

namespace App\Services\Model\Question;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Question;
use App\Http\Resources\Model\QuestionResource;

class QuestionService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Question::class
        );

        $this->resource = QuestionResource::class;
    }
}