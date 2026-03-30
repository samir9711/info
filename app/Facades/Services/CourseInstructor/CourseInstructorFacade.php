<?php

namespace App\Facades\Services\CourseInstructor;

use Illuminate\Support\Facades\Facade;

class CourseInstructorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CourseInstructorService';
    }
}