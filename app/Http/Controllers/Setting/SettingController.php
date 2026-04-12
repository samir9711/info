<?php

namespace App\Http\Controllers\Setting;

use App\Facades\Services\Setting\SettingFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreSettingRequest;
use App\Services\Model\Setting\SettingService;
use Illuminate\Http\Request;

class SettingController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "setting";
        $this->service = SettingFacade::class;
        $this->createRequest = StoreSettingRequest::class;
        $this->updateRequest = StoreSettingRequest::class;
    }

    public function setSuccessMark(Request $request, SettingService $settingService)
    {
        $request->validate([
            'value' => 'required',
        ]);

        $setting = $settingService->setSuccessMark($request->value);

        return response()->json([
            'status' => true,
            'data' => $setting,
        ]);
    }
}
