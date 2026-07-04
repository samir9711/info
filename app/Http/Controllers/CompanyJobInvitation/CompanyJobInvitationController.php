<?php

namespace App\Http\Controllers\CompanyJobInvitation;

use App\Facades\Services\CompanyJobInvitation\CompanyJobInvitationFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCompanyJobInvitationRequest;
use Illuminate\Http\Request;

class CompanyJobInvitationController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "company_job_invitation";
        $this->service = CompanyJobInvitationFacade::class;
        $this->createRequest = StoreCompanyJobInvitationRequest::class;
        $this->updateRequest = StoreCompanyJobInvitationRequest::class;
    }
}