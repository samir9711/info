<?php

namespace App\Services\Model\Instructor;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Instructor;
use App\Http\Resources\Model\InstructorResource;

class InstructorService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Instructor::class
        );

        $this->resource = InstructorResource::class;
    }
}