<?php

namespace App\Facades\Services\CompanySkill;

use Illuminate\Support\Facades\Facade;

class CompanySkillFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CompanySkillService';
    }
}