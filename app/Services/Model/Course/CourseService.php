<?php

namespace App\Services\Model\Course;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Course;
use App\Http\Resources\Model\CourseResource;
use Illuminate\Http\Request;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Support\Facades\DB;

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
        $this->relations = ['category','category.parent','currency','tags','lessons' ,'instructors'];
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

        return DB::transaction(function () use ($data) {
            $course = $this->model::create($data);

            if (array_key_exists('tags', $data)) {
                $course->syncTags((array) $data['tags']);
            }

            if (array_key_exists('instructor_ids', $data)) {
                $course->instructors()->sync($data['instructor_ids'] ?? []);
            }
            return $this->resource::make($course->fresh()->load($this->relations));
        });
    }

    public function update(BasicRequest $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data) {
            $course = $this->model::with($this->relations)->findOrFail($request->id);

            $course->update($data);

            if (array_key_exists('tags', $data)) {
                $course->syncTags((array) $data['tags']);
            }

            if (array_key_exists('instructor_ids', $data)) {
                $course->instructors()->sync($data['instructor_ids'] ?? []);
            }

            return $this->resource::make($course->fresh()->load($this->relations));
        });
    }


    public function myCourses(Request $request): mixed
    {
        $userId = $request->user()->id;

        $courses = Course::query()
            ->with($this->relations)
            ->withCount('lessons')
            ->whereHas('applications', function ($q) use ($userId) {
                $q->where('applicant_id', $userId)
                ->where('status', 1);
            })
            ->get();

        return $this->resource::collection($courses);
    }


}
