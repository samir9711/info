<?php

namespace App\Http\Resources\Model;

use App\Models\CompanyRecommendedCourse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class CompanyRecommendedCourseResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data = $this->initResource(
            ModelColumnsService::getServiceFor(
                CompanyRecommendedCourse::class
            )
        );
        $data['course'] = $this->whenLoaded('course', function () {
            return $this->course ? $this->course->toArray() : null;
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
