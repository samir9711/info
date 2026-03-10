<?php

namespace App\Http\Controllers\Article;

use App\Facades\Services\Article\ArticleFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreArticleRequest;
use Illuminate\Http\Request;

class ArticleController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "article";
        $this->service = ArticleFacade::class;
        $this->createRequest = StoreArticleRequest::class;
        $this->updateRequest = StoreArticleRequest::class;
    }
}