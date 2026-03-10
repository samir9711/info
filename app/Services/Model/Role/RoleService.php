<?php

namespace App\Services\Model\Role;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Role;
use App\Http\Resources\Model\RoleResource;

class RoleService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Role::class
        );

        $this->resource = RoleResource::class;
    }
}