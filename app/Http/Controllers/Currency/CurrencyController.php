<?php

namespace App\Http\Controllers\Currency;

use App\Facades\Services\Currency\CurrencyFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCurrencyRequest;
use Illuminate\Http\Request;

class CurrencyController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "currency";
        $this->service = CurrencyFacade::class;
        $this->createRequest = StoreCurrencyRequest::class;
        $this->updateRequest = StoreCurrencyRequest::class;
    }
}