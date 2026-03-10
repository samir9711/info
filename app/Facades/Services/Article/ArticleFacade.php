<?php

namespace App\Facades\Services\Article;

use Illuminate\Support\Facades\Facade;

class ArticleFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ArticleService';
    }
}