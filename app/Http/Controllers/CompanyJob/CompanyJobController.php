<?php

namespace App\Http\Controllers\CompanyJob;

use App\Facades\Services\CompanyJob\CompanyJobFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCompanyJobRequest;
use Illuminate\Http\Request;

class CompanyJobController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "company_job";
        $this->service = CompanyJobFacade::class;
        $this->createRequest = StoreCompanyJobRequest::class;
        $this->updateRequest = StoreCompanyJobRequest::class;
    }
}