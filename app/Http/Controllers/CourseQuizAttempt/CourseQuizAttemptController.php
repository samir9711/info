<?php

namespace App\Http\Controllers\CourseQuizAttempt;

use App\Facades\Services\CourseQuizAttempt\CourseQuizAttemptFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCourseQuizAttemptRequest;
use App\Http\Requests\Model\SubmitCourseQuizRequest;
use Illuminate\Http\Request;

class CourseQuizAttemptController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "course_quiz_attempt";
        $this->service = CourseQuizAttemptFacade::class;
        $this->createRequest = StoreCourseQuizAttemptRequest::class;
        $this->updateRequest = StoreCourseQuizAttemptRequest::class;
    }

    public function submit(SubmitCourseQuizRequest $request)
    {
        try {
            $data['attempt'] = $this->service::submit($request);

            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
