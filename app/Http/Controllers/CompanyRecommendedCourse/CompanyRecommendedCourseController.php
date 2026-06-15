<?php

namespace App\Http\Controllers\CompanyRecommendedCourse;

use App\Facades\Services\CompanyRecommendedCourse\CompanyRecommendedCourseFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCompanyRecommendedCourseRequest;
use Illuminate\Http\Request;

class CompanyRecommendedCourseController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "company_recommended_course";
        $this->service = CompanyRecommendedCourseFacade::class;
        $this->createRequest = StoreCompanyRecommendedCourseRequest::class;
        $this->updateRequest = StoreCompanyRecommendedCourseRequest::class;
    }
}