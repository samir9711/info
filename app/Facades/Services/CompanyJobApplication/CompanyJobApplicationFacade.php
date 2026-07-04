<?php

namespace App\Facades\Services\CompanyJobApplication;

use Illuminate\Support\Facades\Facade;

class CompanyJobApplicationFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CompanyJobApplicationService';
    }
}