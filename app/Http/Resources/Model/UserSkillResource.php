<?php

namespace App\Http\Resources\Model;

use App\Models\UserSkill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class UserSkillResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data = $this->initResource(
            ModelColumnsService::getServiceFor(
                UserSkill::class
            )
        );
        $data['skill'] = $this->whenLoaded('skill', function () {
            return $this->skill ? $this->skill->toArray() : null;
        });


        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}
