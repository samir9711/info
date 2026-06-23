<?php

namespace App\Http\Controllers\Company;

use App\Facades\Services\Company\CompanyFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCompanyRequest;
use App\Http\Requests\Model\UpdateCompanyProfileRequest;
use Illuminate\Http\Request;

class CompanyController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "company";
        $this->service = CompanyFacade::class;
        $this->createRequest = StoreCompanyRequest::class;
        $this->updateRequest = StoreCompanyRequest::class;
    }


    public function updateProfile(UpdateCompanyProfileRequest $request)
    {
        try {
            return $this->apiResponse(
                $this->service::updateProfile($request)
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function overview(Request $request)
    {
        try {
            return $this->apiResponse(
                $this->service::overview($request)
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function myProfile()
    {
        try {
            return $this->apiResponse(
                $this->service::getMyProfile()
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
