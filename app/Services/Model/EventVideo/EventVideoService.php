<?php

namespace App\Services\Model\EventVideo;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\EventVideo;
use App\Http\Resources\Model\EventVideoResource;

class EventVideoService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = EventVideo::class
        );

        $this->resource = EventVideoResource::class;
    }
}