<?php

namespace App\Services\Model\Answer;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Answer;
use App\Http\Resources\Model\AnswerResource;

class AnswerService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Answer::class
        );

        $this->resource = AnswerResource::class;
    }
}