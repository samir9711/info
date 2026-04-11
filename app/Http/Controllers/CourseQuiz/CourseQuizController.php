<?php

namespace App\Http\Controllers\CourseQuiz;

use App\Facades\Services\CourseQuiz\CourseQuizFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\CourseQuizUpdateRequest;
use App\Http\Requests\Model\StoreCourseQuizRequest;
use Illuminate\Http\Request;
use App\Services\Model\CourseQuiz\CourseQuizService;

class CourseQuizController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "course_quiz";
        $this->service = CourseQuizFacade::class;
        $this->createRequest = StoreCourseQuizRequest::class;
        $this->updateRequest = CourseQuizUpdateRequest::class;
    }


    public function destroy(Request $request)
    {
        try {
            $service = app(CourseQuizService::class);
            $data[$this->key] = $service->deleteOverview($request);

            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
