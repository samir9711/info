<?php

namespace App\Facades\Services\Event;

use Illuminate\Support\Facades\Facade;

class EventFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'EventService';
    }
}