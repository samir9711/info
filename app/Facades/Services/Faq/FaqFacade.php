<?php

namespace App\Facades\Services\Faq;

use Illuminate\Support\Facades\Facade;

class FaqFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'FaqService';
    }
}