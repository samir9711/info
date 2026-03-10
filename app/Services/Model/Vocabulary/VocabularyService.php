<?php

namespace App\Services\Model\Vocabulary;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Vocabulary;
use App\Http\Resources\Model\VocabularyResource;

class VocabularyService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Vocabulary::class
        );

        $this->resource = VocabularyResource::class;
        $this->relations = ['category','category.parent'];
    }
}
