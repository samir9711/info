<?php

namespace App\Services\Model\SingleLesson;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\SingleLesson;
use App\Http\Resources\Model\SingleLessonResource;

class SingleLessonService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = SingleLesson::class
        );

        $this->resource = SingleLessonResource::class;
    }
}