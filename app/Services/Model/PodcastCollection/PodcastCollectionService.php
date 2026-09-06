<?php

namespace App\Services\Model\PodcastCollection;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\PodcastCollection;
use App\Http\Resources\Model\PodcastCollectionResource;

class PodcastCollectionService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = PodcastCollection::class
        );

        $this->resource = PodcastCollectionResource::class;
    }
}