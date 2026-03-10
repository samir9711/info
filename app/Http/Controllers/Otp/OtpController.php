<?php

namespace App\Http\Controllers\Otp;

use App\Facades\Services\Otp\OtpFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreOtpRequest;
use Illuminate\Http\Request;

class OtpController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "otp";
        $this->service = OtpFacade::class;
        $this->createRequest = StoreOtpRequest::class;
        $this->updateRequest = StoreOtpRequest::class;
    }
}