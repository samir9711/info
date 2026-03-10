<?php

namespace App\Facades\Services\Question;

use Illuminate\Support\Facades\Facade;

class QuestionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'QuestionService';
    }
}