<?php

namespace App\Facades\Services\QuizAttemptAnswer;

use Illuminate\Support\Facades\Facade;

class QuizAttemptAnswerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'QuizAttemptAnswerService';
    }
}