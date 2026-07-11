<?php

namespace App\Facades\Services\EventVideo;

use Illuminate\Support\Facades\Facade;

class EventVideoFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'EventVideoService';
    }
}