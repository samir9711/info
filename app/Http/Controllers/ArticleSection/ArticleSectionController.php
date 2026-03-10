<?php

namespace App\Http\Controllers\ArticleSection;

use App\Facades\Services\ArticleSection\ArticleSectionFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreArticleSectionRequest;
use Illuminate\Http\Request;

class ArticleSectionController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "article_section";
        $this->service = ArticleSectionFacade::class;
        $this->createRequest = StoreArticleSectionRequest::class;
        $this->updateRequest = StoreArticleSectionRequest::class;
    }
}