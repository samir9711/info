<?php

namespace App\Http\Controllers\CourseApplication;

use App\Facades\Services\CourseApplication\CourseApplicationFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCourseApplicationRequest;
use Illuminate\Http\Request;

class CourseApplicationController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "course_application";
        $this->service = CourseApplicationFacade::class;
        $this->createRequest = StoreCourseApplicationRequest::class;
        $this->updateRequest = StoreCourseApplicationRequest::class;
    }
}