<?php

namespace App\Facades\Services\Slide;

use Illuminate\Support\Facades\Facade;

class SlideFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'SlideService';
    }
}