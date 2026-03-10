<?php

namespace App\Http\Controllers\LessonView;

use App\Facades\Services\LessonView\LessonViewFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreLessonViewRequest;
use Illuminate\Http\Request;

class LessonViewController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "lesson_view";
        $this->service = LessonViewFacade::class;
        $this->createRequest = StoreLessonViewRequest::class;
        $this->updateRequest = StoreLessonViewRequest::class;
    }
}