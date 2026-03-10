<?php

namespace App\Http\Controllers\Lesson;

use App\Facades\Services\Lesson\LessonFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreLessonRequest;
use Illuminate\Http\Request;

class LessonController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "lesson";
        $this->service = LessonFacade::class;
        $this->createRequest = StoreLessonRequest::class;
        $this->updateRequest = StoreLessonRequest::class;
    }
}