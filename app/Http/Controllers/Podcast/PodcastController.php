<?php

namespace App\Http\Controllers\Podcast;

use App\Facades\Services\Podcast\PodcastFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StorePodcastRequest;
use Illuminate\Http\Request;

class PodcastController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "podcast";
        $this->service = PodcastFacade::class;
        $this->createRequest = StorePodcastRequest::class;
        $this->updateRequest = StorePodcastRequest::class;
    }
}