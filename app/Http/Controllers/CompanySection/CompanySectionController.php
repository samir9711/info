<?php

namespace App\Http\Controllers\CompanySection;

use App\Facades\Services\CompanySection\CompanySectionFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCompanySectionRequest;
use Illuminate\Http\Request;

class CompanySectionController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "company_section";
        $this->service = CompanySectionFacade::class;
        $this->createRequest = StoreCompanySectionRequest::class;
        $this->updateRequest = StoreCompanySectionRequest::class;
    }
}