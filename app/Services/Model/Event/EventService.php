<?php

namespace App\Services\Model\Event;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Event;
use App\Http\Resources\Model\EventResource;

class EventService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Event::class
        );

        $this->resource = EventResource::class;
    }
}