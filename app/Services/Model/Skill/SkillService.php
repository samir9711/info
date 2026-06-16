<?php

namespace App\Services\Model\Skill;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Skill;
use App\Http\Resources\Model\SkillResource;

class SkillService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Skill::class
        );

        $this->resource = SkillResource::class;
    }
}