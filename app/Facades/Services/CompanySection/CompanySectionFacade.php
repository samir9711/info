<?php

namespace App\Facades\Services\CompanySection;

use Illuminate\Support\Facades\Facade;

class CompanySectionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CompanySectionService';
    }
}