<?php

namespace App\Http\Resources\Model;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class CategoryResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data= $this->initResource(
            ModelColumnsService::getServiceFor(
                Category::class
            )
        );

        $data['parent'] = $this->whenLoaded('parent', function () {
            return $this->parent ? $this->parent->toArray() : null;
        });

        $data['courses_count'] = $this->courses_count ?? 0;

        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}
