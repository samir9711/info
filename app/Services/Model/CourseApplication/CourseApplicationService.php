<?php

namespace App\Services\Model\CourseApplication;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CourseApplication;
use App\Http\Resources\Model\CourseApplicationResource;

class CourseApplicationService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CourseApplication::class
        );

        $this->resource = CourseApplicationResource::class;
    }
}