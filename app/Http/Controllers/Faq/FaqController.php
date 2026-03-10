<?php

namespace App\Http\Controllers\Faq;

use App\Facades\Services\Faq\FaqFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreFaqRequest;
use Illuminate\Http\Request;

class FaqController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "faq";
        $this->service = FaqFacade::class;
        $this->createRequest = StoreFaqRequest::class;
        $this->updateRequest = StoreFaqRequest::class;
    }
}