<?php

namespace App\Facades\Services\Auth;

use Illuminate\Support\Facades\Facade;

class CompanyAuthFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'CompanyAuthService';
    }

}
