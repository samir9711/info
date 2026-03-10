<?php

namespace App\Facades\Services\ContactUs;

use Illuminate\Support\Facades\Facade;

class ContactUsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ContactUsService';
    }
}