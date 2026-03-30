<?php

namespace App\Services\Model\CourseInstructor;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CourseInstructor;
use App\Http\Resources\Model\CourseInstructorResource;

class CourseInstructorService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CourseInstructor::class
        );

        $this->resource = CourseInstructorResource::class;
    }


    
}
