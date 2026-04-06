<?php

namespace App\Http\Resources\Model;

use App\Models\CourseFinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class CourseFinancialTransactionResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data = $this->initResource(
            ModelColumnsService::getServiceFor(
                CourseFinancialTransaction::class
            )
        );
        $data['applicant'] = $this->whenLoaded('applicant', function () {
            return $this->applicant ? $this->applicant->toArray() : null;
        });
        $data['course'] = $this->whenLoaded('course', function () {
            return $this->course ? $this->course->toArray() : null;
        });
        $data['instructor'] = $this->whenLoaded('instructor', function () {
            return $this->instructor ? $this->instructor->toArray() : null;
        });



        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}
