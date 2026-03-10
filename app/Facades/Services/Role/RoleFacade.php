<?php

namespace App\Facades\Services\Role;

use Illuminate\Support\Facades\Facade;

class RoleFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'RoleService';
    }
}