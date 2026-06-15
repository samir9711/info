<?php

namespace App\Http\Controllers\CompanySkill;

use App\Facades\Services\CompanySkill\CompanySkillFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCompanySkillRequest;
use Illuminate\Http\Request;

class CompanySkillController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "company_skill";
        $this->service = CompanySkillFacade::class;
        $this->createRequest = StoreCompanySkillRequest::class;
        $this->updateRequest = StoreCompanySkillRequest::class;
    }
}