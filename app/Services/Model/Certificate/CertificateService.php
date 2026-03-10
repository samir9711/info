<?php

namespace App\Services\Model\Certificate;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Certificate;
use App\Http\Resources\Model\CertificateResource;

class CertificateService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Certificate::class
        );

        $this->resource = CertificateResource::class;
    }
}