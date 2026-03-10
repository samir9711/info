<?php

namespace App\Http\Controllers\TermsCondition;

use App\Facades\Services\TermsCondition\TermsConditionFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreTermsConditionRequest;
use Illuminate\Http\Request;

class TermsConditionController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "terms_condition";
        $this->service = TermsConditionFacade::class;
        $this->createRequest = StoreTermsConditionRequest::class;
        $this->updateRequest = StoreTermsConditionRequest::class;
    }
}