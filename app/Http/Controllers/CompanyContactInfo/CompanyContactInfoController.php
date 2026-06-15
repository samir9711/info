<?php

namespace App\Http\Controllers\CompanyContactInfo;

use App\Facades\Services\CompanyContactInfo\CompanyContactInfoFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCompanyContactInfoRequest;
use Illuminate\Http\Request;

class CompanyContactInfoController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "company_contact_info";
        $this->service = CompanyContactInfoFacade::class;
        $this->createRequest = StoreCompanyContactInfoRequest::class;
        $this->updateRequest = StoreCompanyContactInfoRequest::class;
    }
}