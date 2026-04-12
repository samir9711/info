<?php

namespace App\Facades\Services\CourseQuizAttempt;

use Illuminate\Support\Facades\Facade;

class CourseQuizAttemptFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CourseQuizAttemptService';
    }
}