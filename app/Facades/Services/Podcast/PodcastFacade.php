<?php

namespace App\Facades\Services\Podcast;

use Illuminate\Support\Facades\Facade;

class PodcastFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'PodcastService';
    }
}