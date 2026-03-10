<?php

namespace App\Facades\Services\Tag;

use Illuminate\Support\Facades\Facade;

class TagFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'TagService';
    }
}