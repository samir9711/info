<?php

namespace App\Http\Controllers\Vocabulary;

use App\Facades\Services\Vocabulary\VocabularyFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreVocabularyRequest;
use Illuminate\Http\Request;

class VocabularyController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "vocabulary";
        $this->service = VocabularyFacade::class;
        $this->createRequest = StoreVocabularyRequest::class;
        $this->updateRequest = StoreVocabularyRequest::class;
    }
}