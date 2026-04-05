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


    public function myCourses(Request $request)
    {
        try {
            $data = $this->service::myCourses($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function myInstructorCourses(Request $request)
    {
        try {
            $data = $this->service::myInstructorCourses($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function approve(Request $request)
    {
        try {
            $data = $this->service::approve($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function reject(Request $request)
    {
        try {
            $data = $this->service::reject($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
