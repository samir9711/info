<?php

namespace App\Http\Controllers\CourseInstructor;

use App\Facades\Services\CourseInstructor\CourseInstructorFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCourseInstructorRequest;
use Illuminate\Http\Request;

class CourseInstructorController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "course_instructor";
        $this->service = CourseInstructorFacade::class;
        $this->createRequest = StoreCourseInstructorRequest::class;
        $this->updateRequest = StoreCourseInstructorRequest::class;
    }
}