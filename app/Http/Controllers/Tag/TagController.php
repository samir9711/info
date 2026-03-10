<?php

namespace App\Http\Controllers\Tag;

use App\Facades\Services\Tag\TagFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreTagRequest;
use Illuminate\Http\Request;

class TagController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "tag";
        $this->service = TagFacade::class;
        $this->createRequest = StoreTagRequest::class;
        $this->updateRequest = StoreTagRequest::class;
    }
}