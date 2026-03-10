<?php

namespace App\Facades\Services\LessonQuiz;

use Illuminate\Support\Facades\Facade;

class LessonQuizFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'LessonQuizService';
    }
}