<?php

namespace App\Facades\Services\TermsCondition;

use Illuminate\Support\Facades\Facade;

class TermsConditionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'TermsConditionService';
    }
}