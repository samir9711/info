<?php

namespace App\Services\Model\LessonView;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\LessonView;
use App\Http\Resources\Model\LessonViewResource;

class LessonViewService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = LessonView::class
        );

        $this->resource = LessonViewResource::class;
    }
}