<?php

namespace App\Http\Controllers\Certificate;

use App\Facades\Services\Certificate\CertificateFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCertificateRequest;
use Illuminate\Http\Request;

class CertificateController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "certificate";
        $this->service = CertificateFacade::class;
        $this->createRequest = StoreCertificateRequest::class;
        $this->updateRequest = StoreCertificateRequest::class;
    }
}