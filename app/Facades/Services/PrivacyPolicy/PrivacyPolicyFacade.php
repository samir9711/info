<?php

namespace App\Facades\Services\PrivacyPolicy;

use Illuminate\Support\Facades\Facade;

class PrivacyPolicyFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'PrivacyPolicyService';
    }
}