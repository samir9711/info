<?php

namespace App\Facades\Services\Company;

use Illuminate\Support\Facades\Facade;

class CompanyFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CompanyService';
    }
}