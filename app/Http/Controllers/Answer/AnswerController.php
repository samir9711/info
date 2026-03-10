<?php

namespace App\Http\Controllers\Answer;

use App\Facades\Services\Answer\AnswerFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreAnswerRequest;
use Illuminate\Http\Request;

class AnswerController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "answer";
        $this->service = AnswerFacade::class;
        $this->createRequest = StoreAnswerRequest::class;
        $this->updateRequest = StoreAnswerRequest::class;
    }
}