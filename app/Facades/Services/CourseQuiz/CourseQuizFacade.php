<?php

namespace App\Facades\Services\CourseQuiz;

use Illuminate\Support\Facades\Facade;

class CourseQuizFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CourseQuizService';
    }
}