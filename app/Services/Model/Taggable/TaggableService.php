<?php

namespace App\Services\Model\Taggable;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Taggable;
use App\Http\Resources\Model\TaggableResource;

class TaggableService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Taggable::class
        );

        $this->resource = TaggableResource::class;
    }
}