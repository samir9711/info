<?php

namespace App\Http\Controllers\Auth;

use App\Facades\Services\Auth\MinistryAuthFacade;
use App\Http\Controllers\FatherAuthController;
use App\Http\Traits\GeneralTrait;
use Illuminate\Http\Request;

class MinisrtyAuthController extends FatherAuthController
{
    use GeneralTrait;


    protected function setVariables(): void
    {
        $this->key = 'ministry';
        $this->service = MinistryAuthFacade::class;
    }


    public function changePassword(Request $request)
    {
        try {
            $data = $this->service::changePassword($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function logout(Request $request)
    {
        try {
            $deviceToken = $request->input('token_device');
            $ministry = $request->user('ministry');

            if ($ministry) {
                $ministry->currentAccessToken()?->delete();
            }

            if ($deviceToken) {
                $this->service::revokeDeviceToken($request);
            }

            return $this->apiResponse(['message' => __('messages.logged_out')]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
