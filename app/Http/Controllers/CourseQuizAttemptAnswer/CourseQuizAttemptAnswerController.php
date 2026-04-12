<?php

namespace App\Http\Controllers\CourseQuizAttemptAnswer;

use App\Facades\Services\CourseQuizAttemptAnswer\CourseQuizAttemptAnswerFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCourseQuizAttemptAnswerRequest;
use Illuminate\Http\Request;

class CourseQuizAttemptAnswerController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "course_quiz_attempt_answer";
        $this->service = CourseQuizAttemptAnswerFacade::class;
        $this->createRequest = StoreCourseQuizAttemptAnswerRequest::class;
        $this->updateRequest = StoreCourseQuizAttemptAnswerRequest::class;
    }
}