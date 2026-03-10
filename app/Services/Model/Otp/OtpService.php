<?php

namespace App\Services\Model\Otp;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Otp;
use App\Http\Resources\Model\OtpResource;

class OtpService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Otp::class
        );

        $this->resource = OtpResource::class;
    }
}