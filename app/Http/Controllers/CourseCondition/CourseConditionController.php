<?php

namespace App\Http\Controllers\CourseCondition;

use App\Facades\Services\CourseCondition\CourseConditionFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCourseConditionRequest;
use Illuminate\Http\Request;

class CourseConditionController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "course_condition";
        $this->service = CourseConditionFacade::class;
        $this->createRequest = StoreCourseConditionRequest::class;
        $this->updateRequest = StoreCourseConditionRequest::class;
    }
}