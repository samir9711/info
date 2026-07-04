<?php

namespace App\Services\Model\CompanyJobInvitation;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CompanyJobInvitation;
use App\Http\Resources\Model\CompanyJobInvitationResource;

class CompanyJobInvitationService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CompanyJobInvitation::class
        );

        $this->resource = CompanyJobInvitationResource::class;
    }
}