<?php

namespace App\Http\Controllers\PrivacyPolicy;

use App\Facades\Services\PrivacyPolicy\PrivacyPolicyFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StorePrivacyPolicyRequest;
use Illuminate\Http\Request;

class PrivacyPolicyController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "privacy_policy";
        $this->service = PrivacyPolicyFacade::class;
        $this->createRequest = StorePrivacyPolicyRequest::class;
        $this->updateRequest = StorePrivacyPolicyRequest::class;
    }
}