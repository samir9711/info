<?php

namespace App\Facades\Services\CourseApplication;

use Illuminate\Support\Facades\Facade;

class CourseApplicationFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CourseApplicationService';
    }
}