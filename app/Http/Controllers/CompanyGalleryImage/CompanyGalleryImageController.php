<?php

namespace App\Http\Controllers\CompanyGalleryImage;

use App\Facades\Services\CompanyGalleryImage\CompanyGalleryImageFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCompanyGalleryImageRequest;
use Illuminate\Http\Request;

class CompanyGalleryImageController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "company_gallery_image";
        $this->service = CompanyGalleryImageFacade::class;
        $this->createRequest = StoreCompanyGalleryImageRequest::class;
        $this->updateRequest = StoreCompanyGalleryImageRequest::class;
    }
}