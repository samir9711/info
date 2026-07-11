<?php

namespace App\Http\Controllers\EventVideo;

use App\Facades\Services\EventVideo\EventVideoFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreEventVideoRequest;
use Illuminate\Http\Request;

class EventVideoController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "event_video";
        $this->service = EventVideoFacade::class;
        $this->createRequest = StoreEventVideoRequest::class;
        $this->updateRequest = StoreEventVideoRequest::class;
    }
}