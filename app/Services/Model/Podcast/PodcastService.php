<?php

namespace App\Services\Model\Podcast;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Podcast;
use App\Http\Resources\Model\PodcastResource;

class PodcastService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Podcast::class
        );

        $this->resource = PodcastResource::class;
    }
}