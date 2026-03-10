<?php

namespace App\Services\Model\Favorite;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Favorite;
use App\Http\Resources\Model\FavoriteResource;

class FavoriteService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Favorite::class
        );

        $this->resource = FavoriteResource::class;
    }
}