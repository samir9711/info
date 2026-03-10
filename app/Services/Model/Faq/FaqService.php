<?php

namespace App\Services\Model\Faq;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Faq;
use App\Http\Resources\Model\FaqResource;

class FaqService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Faq::class
        );

        $this->resource = FaqResource::class;
    }
}