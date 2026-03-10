<?php

namespace App\Http\Controllers\PrivacyUsage;

use App\Facades\Services\PrivacyUsage\PrivacyUsageFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StorePrivacyUsageRequest;
use Illuminate\Http\Request;

class PrivacyUsageController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "privacy_usage";
        $this->service = PrivacyUsageFacade::class;
        $this->createRequest = StorePrivacyUsageRequest::class;
        $this->updateRequest = StorePrivacyUsageRequest::class;
    }
}