<?php

namespace App\Services\Model\Setting;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Setting;
use App\Http\Resources\Model\SettingResource;

class SettingService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Setting::class
        );

        $this->resource = SettingResource::class;
    }
}