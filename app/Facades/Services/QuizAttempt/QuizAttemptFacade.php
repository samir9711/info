<?php

namespace App\Facades\Services\QuizAttempt;

use Illuminate\Support\Facades\Facade;

class QuizAttemptFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'QuizAttemptService';
    }
}