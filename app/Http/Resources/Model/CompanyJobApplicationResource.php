<?php

namespace App\Http\Resources\Model;

use App\Models\CompanyJobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class CompanyJobApplicationResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data = $this->initResource(
            ModelColumnsService::getServiceFor(
                CompanyJobApplication::class
            )
        );

        $data['companyJob'] = $this->whenLoaded('companyJob', function () {
            return $this->companyJob ? $this->companyJob->toArray() : null;
        });

        $data['user'] = $this->whenLoaded('user', function () {
            return $this->user ? $this->user->toArray() : null;
        });

        $data['company'] = $this->whenLoaded('company', function () {
            return $this->company ? $this->company->toArray() : null;
        });

        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}
