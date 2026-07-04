<?php

namespace App\Facades\Services\CompanyJobInvitation;

use Illuminate\Support\Facades\Facade;

class CompanyJobInvitationFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CompanyJobInvitationService';
    }
}