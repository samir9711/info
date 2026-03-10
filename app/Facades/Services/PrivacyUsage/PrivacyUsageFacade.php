<?php

namespace App\Facades\Services\PrivacyUsage;

use Illuminate\Support\Facades\Facade;

class PrivacyUsageFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'PrivacyUsageService';
    }
}