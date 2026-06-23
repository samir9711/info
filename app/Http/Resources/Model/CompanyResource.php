<?php

namespace App\Http\Resources\Model;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class CompanyResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data = $this->initResource(
            ModelColumnsService::getServiceFor(Company::class)
        );

        $data['sections'] = $this->whenLoaded('sections', function () {
            return $this->sections ? $this->sections->toArray() : null;
        });

        $data['skills'] = $this->whenLoaded('skills', function () {
            return $this->skills ? $this->skills->toArray() : null;
        });

        $data['gallery_images'] = $this->whenLoaded('galleryImages', function () {
            return $this->galleryImages ? $this->galleryImages->toArray() : null;
        });

        $data['jobs'] = $this->whenLoaded('jobs', function () {
            return $this->jobs ? $this->jobs->toArray() : null;
        });

        $data['recommended_courses'] = $this->whenLoaded('recommendedCourses', function () {
            return $this->recommendedCourses ? $this->recommendedCourses->toArray() : null;
        });

        $data['contact_info'] = $this->whenLoaded('contactInfo', function () {
            return $this->contactInfo ? $this->contactInfo->toArray() : null;
        });

        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}
