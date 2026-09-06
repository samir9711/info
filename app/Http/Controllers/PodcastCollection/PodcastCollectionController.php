<?php

namespace App\Http\Controllers\PodcastCollection;

use App\Facades\Services\PodcastCollection\PodcastCollectionFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StorePodcastCollectionRequest;
use Illuminate\Http\Request;

class PodcastCollectionController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "podcast_collection";
        $this->service = PodcastCollectionFacade::class;
        $this->createRequest = StorePodcastCollectionRequest::class;
        $this->updateRequest = StorePodcastCollectionRequest::class;
    }
}