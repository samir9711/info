<?php

namespace App\Http\Controllers\Quiz;

use App\Facades\Services\Quiz\QuizFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreQuizRequest;
use Illuminate\Http\Request;

class QuizController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "quiz";
        $this->service = QuizFacade::class;
        $this->createRequest = StoreQuizRequest::class;
        $this->updateRequest = StoreQuizRequest::class;
    }
}