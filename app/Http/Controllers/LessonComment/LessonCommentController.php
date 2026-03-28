<?php

namespace App\Http\Controllers\LessonComment;

use App\Facades\Services\LessonComment\LessonCommentFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreLessonCommentRequest;
use Illuminate\Http\Request;

class LessonCommentController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "lesson_comment";
        $this->service = LessonCommentFacade::class;
        $this->createRequest = StoreLessonCommentRequest::class;
        $this->updateRequest = StoreLessonCommentRequest::class;
    }
}