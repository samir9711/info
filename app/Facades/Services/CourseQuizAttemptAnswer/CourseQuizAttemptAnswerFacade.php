<?php

namespace App\Facades\Services\CourseQuizAttemptAnswer;

use Illuminate\Support\Facades\Facade;

class CourseQuizAttemptAnswerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CourseQuizAttemptAnswerService';
    }
}