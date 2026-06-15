<?php

namespace App\Http\Resources\Model;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class CompanyJobResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data = $this->initResource(
            ModelColumnsService::getServiceFor(
                CompanyJob::class
            )
        );
        $data['company'] = $this->whenLoaded('company', function () {
            return $this->company ? $this->company->toArray() : null;
        });
        $data['currency'] = $this->whenLoaded('currency', function () {
            return $this->currency ? $this->currency->toArray() : null;
        });
        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}
