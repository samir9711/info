<?php

namespace App\Facades\Services\Lesson;

use Illuminate\Support\Facades\Facade;

class LessonFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'LessonService';
    }
}