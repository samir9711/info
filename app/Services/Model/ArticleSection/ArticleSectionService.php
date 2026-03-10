<?php

namespace App\Services\Model\ArticleSection;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\ArticleSection;
use App\Http\Resources\Model\ArticleSectionResource;

class ArticleSectionService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = ArticleSection::class
        );

        $this->resource = ArticleSectionResource::class;
    }
}