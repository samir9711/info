<?php

namespace App\Services\Model\Slide;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Slide;
use App\Http\Resources\Model\SlideResource;

class SlideService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Slide::class
        );

        $this->resource = SlideResource::class;
    }
}