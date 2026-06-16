<?php

namespace App\Facades\Services\Skill;

use Illuminate\Support\Facades\Facade;

class SkillFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'SkillService';
    }
}