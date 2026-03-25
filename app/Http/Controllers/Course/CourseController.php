<?php

namespace App\Http\Controllers\Course;

use App\Facades\Services\Course\CourseFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCourseRequest;
use Illuminate\Http\Request;

class CourseController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "course";
        $this->service = CourseFacade::class;
        $this->createRequest = StoreCourseRequest::class;
        $this->updateRequest = StoreCourseRequest::class;
    }
}
