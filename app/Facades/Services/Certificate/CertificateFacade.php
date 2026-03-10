<?php

namespace App\Facades\Services\Certificate;

use Illuminate\Support\Facades\Facade;

class CertificateFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CertificateService';
    }
}