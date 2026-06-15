<?php

namespace App\Services\Model\Company;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Company;
use App\Http\Resources\Model\CompanyResource;

class CompanyService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Company::class
        );

        $this->resource = CompanyResource::class;
    }
}