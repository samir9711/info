<?php

namespace App\Http\Controllers\SingleLesson;

use App\Facades\Services\SingleLesson\SingleLessonFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreSingleLessonRequest;
use Illuminate\Http\Request;

class SingleLessonController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "single_lesson";
        $this->service = SingleLessonFacade::class;
        $this->createRequest = StoreSingleLessonRequest::class;
        $this->updateRequest = StoreSingleLessonRequest::class;
    }
}