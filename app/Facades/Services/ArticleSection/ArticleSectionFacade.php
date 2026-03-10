<?php

namespace App\Facades\Services\ArticleSection;

use Illuminate\Support\Facades\Facade;

class ArticleSectionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ArticleSectionService';
    }
}