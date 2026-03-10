<?php

namespace App\Facades\Services\Taggable;

use Illuminate\Support\Facades\Facade;

class TaggableFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'TaggableService';
    }
}