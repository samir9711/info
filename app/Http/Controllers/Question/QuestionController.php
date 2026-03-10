<?php

namespace App\Http\Controllers\Question;

use App\Facades\Services\Question\QuestionFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreQuestionRequest;
use Illuminate\Http\Request;

class QuestionController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "question";
        $this->service = QuestionFacade::class;
        $this->createRequest = StoreQuestionRequest::class;
        $this->updateRequest = StoreQuestionRequest::class;
    }
}