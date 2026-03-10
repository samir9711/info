<?php

namespace App\Http\Controllers\Role;

use App\Facades\Services\Role\RoleFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreRoleRequest;
use Illuminate\Http\Request;

class RoleController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "role";
        $this->service = RoleFacade::class;
        $this->createRequest = StoreRoleRequest::class;
        $this->updateRequest = StoreRoleRequest::class;
    }
}