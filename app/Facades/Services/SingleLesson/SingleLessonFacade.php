<?php

namespace App\Facades\Services\SingleLesson;

use Illuminate\Support\Facades\Facade;

class SingleLessonFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'SingleLessonService';
    }
}