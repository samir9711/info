<?php

namespace App\Services\Model\Tag;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Tag;
use App\Http\Resources\Model\TagResource;

class TagService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Tag::class
        );

        $this->resource = TagResource::class;
    }
}