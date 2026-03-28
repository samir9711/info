<?php

namespace App\Services\Model\Course;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Course;
use App\Http\Resources\Model\CourseResource;
use Illuminate\Http\Request;
use App\Http\Requests\Basic\BasicRequest;

class CourseService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Course::class
        );

        $this->resource = CourseResource::class;
        $this->relations = ['category','category.parent','currency','tags','lessons'];
    }

    protected function allQuery(): object
    {
        return $this->model::withFilters()
            ->with($this->relations)
            ->withCount('lessons')
            ->orderByDesc('created_at');
    }


    public function create(BasicRequest $request): mixed
    {
        $data = $request->validated();

        $course = $this->model::create($data);

        // sync tags if provided
        if (array_key_exists('tags', $data)) {
            $course->syncTags((array) $data['tags']);
        }

        return $this->resource::make($course->load($this->relations));
    }

    public function update(BasicRequest $request): mixed
    {
        $data = $request->validated();

        $course = $this->model::with($this->relations)->findOrFail($request->id);

        $course->update($data);

        if (array_key_exists('tags', $data)) {
            $course->syncTags((array) $data['tags']);
        }

        return $this->resource::make($course->fresh()->load($this->relations));
    }


}
