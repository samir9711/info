<?php

namespace App\Http\Controllers\Event;

use App\Facades\Services\Event\EventFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreEventRequest;
use Illuminate\Http\Request;

class EventController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "event";
        $this->service = EventFacade::class;
        $this->createRequest = StoreEventRequest::class;
        $this->updateRequest = StoreEventRequest::class;
    }
}