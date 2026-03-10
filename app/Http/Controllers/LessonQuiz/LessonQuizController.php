<?php

namespace App\Http\Controllers\LessonQuiz;

use App\Facades\Services\LessonQuiz\LessonQuizFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreLessonQuizRequest;
use Illuminate\Http\Request;

class LessonQuizController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "lesson_quiz";
        $this->service = LessonQuizFacade::class;
        $this->createRequest = StoreLessonQuizRequest::class;
        $this->updateRequest = StoreLessonQuizRequest::class;
    }
}