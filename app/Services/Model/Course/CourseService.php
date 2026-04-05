<?php

namespace App\Services\Model\Course;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Course;
use App\Http\Resources\Model\CourseResource;
use Illuminate\Http\Request;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;


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
        $query = $this->model::withFilters()
            ->with($this->relations)
            ->withCount([
                'lessons',
                'applications as accepted_students_count' => function ($q) {
                    $q->where('status', 1);
                },
            ])
            ->orderByDesc('created_at');

        $admin = request()->user('admin');
        $user = request()->user('user') ?? request()->user();

        if (!$admin && $user) {
            $query->where('approval_status', 1);
        }

        return $query;
    }


    public function create(BasicRequest $request): mixed
    {
        $data = $request->validated();

        $admin = $request->user('admin');
        $instructor = $request->user('instructor');

        if (!$admin && !$instructor) {
            abort(401);
        }

        return DB::transaction(function () use ($data, $admin, $instructor) {
            $courseData = Arr::except($data, ['tags', 'instructor_ids']);

            if ($admin) {
                $courseData['approval_status'] = 1;
                $courseData['is_platform_owned'] = array_key_exists('is_platform_owned', $data)
                    ? (bool) $data['is_platform_owned']
                    : true;

                $courseData['created_by'] = null;
            }

            if ($instructor) {
                $courseData['approval_status'] = 0;
                $courseData['is_platform_owned'] = false;
                $courseData['created_by'] = $instructor->id;
            }

            $course = $this->model::create($courseData);

            if (array_key_exists('tags', $data)) {
                $course->syncTags((array) $data['tags']);
            }

            if (array_key_exists('instructor_ids', $data)) {
                $course->instructors()->sync($data['instructor_ids'] ?? []);
            } elseif ($instructor) {
                $course->instructors()->sync([$instructor->id]);
            }

            return $this->resource::make($course->fresh()->load($this->relations));
        });
    }

    public function update(BasicRequest $request): mixed
    {
        $data = $request->validated();

        $admin = $request->user('admin');
        $instructor = $request->user('instructor');

        if (!$admin && !$instructor) {
            abort(401);
        }

        return DB::transaction(function () use ($request, $data, $admin, $instructor) {
            $course = $this->model::with($this->relations)->findOrFail($request->id);

            if ($instructor) {
                if ((int) $course->created_by !== (int) $instructor->id) {
                    abort(403, 'You are not allowed to edit this course.');
                }
            }

            $courseData = Arr::except($data, ['tags', 'instructor_ids']);

            if ($instructor) {
                $courseData['approval_status'] = 0;
                $courseData['is_platform_owned'] = false;
            }

            if ($admin && array_key_exists('is_platform_owned', $data)) {
                $courseData['is_platform_owned'] = (bool) $data['is_platform_owned'];
            }

            $course->update($courseData);

            if (array_key_exists('tags', $data)) {
                $course->syncTags((array) $data['tags']);
            }

            if ($admin && array_key_exists('instructor_ids', $data)) {
                $course->instructors()->sync($data['instructor_ids'] ?? []);
            }

            return $this->resource::make($course->fresh()->load($this->relations));
        });
    }

    public function approve(Request $request): mixed
    {
        $admin = $request->user('admin');

        if (!$admin) {
            abort(403, 'Only admin can approve courses.');
        }

        $request->validate([
            'profit_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        return DB::transaction(function () use ($request) {
            $course = $this->model::findOrFail($request->id);

            $course->update([
                'approval_status' => 1,
                'profit_percentage' => $request->input('profit_percentage'),
                'rejection_reason' => null,
            ]);

            return $this->resource::make($course->fresh()->load($this->relations));
        });
    }

    public function reject(Request $request): mixed
    {
        $admin = $request->user('admin');

        if (!$admin) {
            abort(403, 'Only admin can reject courses.');
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        return DB::transaction(function () use ($request) {
            $course = $this->model::findOrFail($request->id);

            $course->update([
                'approval_status' => 2,
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

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


    public function myInstructorCourses(Request $request): mixed
    {
        $instructor = $request->user('instructor');

        if (!$instructor) {
            abort(401);
        }

        $courses = Course::query()
            ->with($this->relations)
            ->withCount([
                'lessons',
                'applications as accepted_students_count' => function ($q) {
                    $q->where('status', 1);
                },
            ])
            ->where('created_by', $instructor->id)
            ->orderByDesc('created_at')
            ->get();

        return $this->resource::collection($courses);
    }


}
