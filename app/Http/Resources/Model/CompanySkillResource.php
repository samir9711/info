<?php

namespace App\Http\Resources\Model;

use App\Models\CompanySkill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class CompanySkillResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data = $this->initResource(
            ModelColumnsService::getServiceFor(
                CompanySkill::class
            )
        );
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
