<?php

namespace App\Http\Controllers\Slide;

use App\Facades\Services\Slide\SlideFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreSlideRequest;
use Illuminate\Http\Request;

class SlideController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "slide";
        $this->service = SlideFacade::class;
        $this->createRequest = StoreSlideRequest::class;
        $this->updateRequest = StoreSlideRequest::class;
    }
}