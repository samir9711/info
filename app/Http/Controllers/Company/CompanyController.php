<?php

namespace App\Http\Controllers\Company;

use App\Facades\Services\Company\CompanyFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCompanyRequest;
use Illuminate\Http\Request;

class CompanyController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "company";
        $this->service = CompanyFacade::class;
        $this->createRequest = StoreCompanyRequest::class;
        $this->updateRequest = StoreCompanyRequest::class;
    }
}