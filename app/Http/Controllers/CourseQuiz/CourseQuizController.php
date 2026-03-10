<?php

namespace App\Http\Controllers\CourseQuiz;

use App\Facades\Services\CourseQuiz\CourseQuizFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCourseQuizRequest;
use Illuminate\Http\Request;

class CourseQuizController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "course_quiz";
        $this->service = CourseQuizFacade::class;
        $this->createRequest = StoreCourseQuizRequest::class;
        $this->updateRequest = StoreCourseQuizRequest::class;
    }
}