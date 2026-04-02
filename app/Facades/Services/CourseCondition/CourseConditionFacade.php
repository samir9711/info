<?php

namespace App\Facades\Services\CourseCondition;

use Illuminate\Support\Facades\Facade;

class CourseConditionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CourseConditionService';
    }
}