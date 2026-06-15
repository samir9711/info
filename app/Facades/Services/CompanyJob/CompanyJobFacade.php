<?php

namespace App\Facades\Services\CompanyJob;

use Illuminate\Support\Facades\Facade;

class CompanyJobFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CompanyJobService';
    }
}