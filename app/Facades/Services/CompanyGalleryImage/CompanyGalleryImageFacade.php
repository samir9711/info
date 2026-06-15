<?php

namespace App\Facades\Services\CompanyGalleryImage;

use Illuminate\Support\Facades\Facade;

class CompanyGalleryImageFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CompanyGalleryImageService';
    }
}