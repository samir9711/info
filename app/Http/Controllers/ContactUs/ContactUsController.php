<?php

namespace App\Http\Controllers\ContactUs;

use App\Facades\Services\ContactUs\ContactUsFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreContactUsRequest;
use Illuminate\Http\Request;

class ContactUsController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "contact_us";
        $this->service = ContactUsFacade::class;
        $this->createRequest = StoreContactUsRequest::class;
        $this->updateRequest = StoreContactUsRequest::class;
    }
}