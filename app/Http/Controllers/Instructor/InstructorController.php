<?php

namespace App\Http\Controllers\Instructor;

use App\Facades\Services\Instructor\InstructorFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreInstructorRequest;
use Illuminate\Http\Request;

class InstructorController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "instructor";
        $this->service = InstructorFacade::class;
        $this->createRequest = StoreInstructorRequest::class;
        $this->updateRequest = StoreInstructorRequest::class;
    }
}