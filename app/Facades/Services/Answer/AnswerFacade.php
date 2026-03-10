<?php

namespace App\Facades\Services\Answer;

use Illuminate\Support\Facades\Facade;

class AnswerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'AnswerService';
    }
}