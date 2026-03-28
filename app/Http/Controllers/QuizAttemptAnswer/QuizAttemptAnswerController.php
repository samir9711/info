<?php

namespace App\Http\Controllers\QuizAttemptAnswer;

use App\Facades\Services\QuizAttemptAnswer\QuizAttemptAnswerFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreQuizAttemptAnswerRequest;
use Illuminate\Http\Request;

class QuizAttemptAnswerController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "quiz_attempt_answer";
        $this->service = QuizAttemptAnswerFacade::class;
        $this->createRequest = StoreQuizAttemptAnswerRequest::class;
        $this->updateRequest = StoreQuizAttemptAnswerRequest::class;
    }
}