<?php

namespace App\Facades\Services\UserSkill;

use Illuminate\Support\Facades\Facade;

class UserSkillFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'UserSkillService';
    }
}