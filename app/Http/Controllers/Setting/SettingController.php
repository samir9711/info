<?php

namespace App\Http\Controllers\Setting;

use App\Facades\Services\Setting\SettingFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreSettingRequest;
use Illuminate\Http\Request;

class SettingController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "setting";
        $this->service = SettingFacade::class;
        $this->createRequest = StoreSettingRequest::class;
        $this->updateRequest = StoreSettingRequest::class;
    }
}