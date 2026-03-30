<?php

namespace App\Facades\Services\Instructor;

use Illuminate\Support\Facades\Facade;

class InstructorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'InstructorService';
    }
}