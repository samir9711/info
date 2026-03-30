<?php

namespace App\Http\Controllers\InstructorRating;

use App\Facades\Services\InstructorRating\InstructorRatingFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\InstructorIdRequest;
use App\Http\Requests\Model\StoreInstructorRatingRequest;
use Illuminate\Http\Request;

class InstructorRatingController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "instructor_rating";
        $this->service = InstructorRatingFacade::class;
        $this->createRequest = StoreInstructorRatingRequest::class;
        $this->updateRequest = StoreInstructorRatingRequest::class;
    }

    public function byInstructor(InstructorIdRequest $request)
    {
        try {
            $data = InstructorRatingFacade::byInstructor($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function summary(InstructorIdRequest $request)
    {
        try {
            $data = InstructorRatingFacade::summary($request);
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
