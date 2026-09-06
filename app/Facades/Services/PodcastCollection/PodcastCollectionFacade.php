<?php

namespace App\Facades\Services\PodcastCollection;

use Illuminate\Support\Facades\Facade;

class PodcastCollectionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'PodcastCollectionService';
    }
}