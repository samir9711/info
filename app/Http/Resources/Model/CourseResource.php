<?php

namespace App\Http\Resources\Model;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class CourseResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data= $this->initResource(
            ModelColumnsService::getServiceFor(
                Course::class
            )
        );

        $data['category'] = $this->whenLoaded('category', function () {
            return $this->category ? $this->category->toArray() : null;
        });
        $data['currency'] = $this->whenLoaded('currency', function () {
            return $this->currency ? $this->currency->toArray() : null;
        });
        $data['tags'] = $this->whenLoaded('tags', function () {
            return $this->tags ? $this->tags->toArray() : null;
        });
        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}
