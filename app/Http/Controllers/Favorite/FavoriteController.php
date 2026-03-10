<?php

namespace App\Http\Controllers\Favorite;

use App\Facades\Services\Favorite\FavoriteFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreFavoriteRequest;
use Illuminate\Http\Request;

class FavoriteController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "favorite";
        $this->service = FavoriteFacade::class;
        $this->createRequest = StoreFavoriteRequest::class;
        $this->updateRequest = StoreFavoriteRequest::class;
    }
}