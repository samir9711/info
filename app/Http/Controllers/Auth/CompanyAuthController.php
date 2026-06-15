<?php

namespace App\Http\Controllers\Auth;


use App\Facades\Services\Auth\CompanyAuthFacade;
use App\Http\Controllers\FatherAuthController;
use App\Http\Traits\GeneralTrait;
use Illuminate\Http\Request;

class CompanyAuthController extends FatherAuthController
{
    use GeneralTrait;


    protected function setVariables(): void
    {
        $this->key = 'company';
        $this->service = CompanyAuthFacade::class;
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
            $company = $request->user('company');

            if ($company) {
                $company->currentAccessToken()?->delete();
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
