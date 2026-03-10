<?php

namespace App\Http\Controllers\Taggable;

use App\Facades\Services\Taggable\TaggableFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreTaggableRequest;
use Illuminate\Http\Request;

class TaggableController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "taggable";
        $this->service = TaggableFacade::class;
        $this->createRequest = StoreTaggableRequest::class;
        $this->updateRequest = StoreTaggableRequest::class;
    }
}