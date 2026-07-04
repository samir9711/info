<?php

namespace App\Http\Controllers\CompanyJobApplication;

use App\Facades\Services\CompanyJobApplication\CompanyJobApplicationFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCompanyJobApplicationRequest;
use Illuminate\Http\Request;

class CompanyJobApplicationController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "company_job_application";
        $this->service = CompanyJobApplicationFacade::class;
        $this->createRequest = StoreCompanyJobApplicationRequest::class;
        $this->updateRequest = StoreCompanyJobApplicationRequest::class;
    }

    public function myApplications(Request $request)
    {
        try {

            return $this->apiResponse(
                $this->service::myApplications($request)
            );

        } catch (\Exception $e) {

            return $this->handleException($e);

        }
    }
    public function companyApplications(Request $request)
    {
        try {

            return $this->apiResponse(
                $this->service::companyApplications($request)
            );

        } catch (\Exception $e) {

            return $this->handleException($e);

        }
    }
}
