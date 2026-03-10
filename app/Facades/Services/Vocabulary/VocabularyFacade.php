<?php

namespace App\Facades\Services\Vocabulary;

use Illuminate\Support\Facades\Facade;

class VocabularyFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'VocabularyService';
    }
}