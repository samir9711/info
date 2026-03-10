<?php

namespace App\Facades\Services\Quiz;

use Illuminate\Support\Facades\Facade;

class QuizFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'QuizService';
    }
}