<?php

namespace App\Facades\Services\LessonView;

use Illuminate\Support\Facades\Facade;

class LessonViewFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'LessonViewService';
    }
}