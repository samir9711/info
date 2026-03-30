<?php

namespace App\Facades\Services\InstructorRating;

use Illuminate\Support\Facades\Facade;

class InstructorRatingFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'InstructorRatingService';
    }
}