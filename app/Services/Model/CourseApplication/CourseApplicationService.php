<?php

namespace App\Services\Model\CourseApplication;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CourseApplication;
use App\Http\Resources\Model\CourseApplicationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Basic\BasicRequest;

class CourseApplicationService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CourseApplication::class
        );

        $this->resource = CourseApplicationResource::class;
        $this->relations = ['course','course.category','course.category.parent','applicant'];
    }

    protected function allQuery(): object
    {
        $request = request();

        $query = $this->model::withFilters()
            ->with($this->relations)
            ->orderBy('created_at', 'desc');


        $isAdmin = (bool) $request->user('admin');

        if (! $isAdmin) {

            $applicant = $request->user('user') ?? auth()->user();
            if ($applicant) {
                $query->where('applicant_id', $applicant->id);
            } else {

                $query->whereRaw('0 = 1');
            }
        }

        return $query;
    }

    /**
     * Show: if not admin, allow only owner (applicant).
     */
    public function show(Request $request): mixed
    {
        $this->object = $this->model::with($this->relations)
            ->withTrashed()
            ->withCount($this->countRelations)
            ->findOrFail($request->id);

        $isAdmin = (bool) $request->user('admin');
        if (! $isAdmin) {
            $applicant = $request->user('user') ?? auth()->user();
            if (! $applicant || $this->object->applicant_id !== $applicant->id) {
                throw new HttpResponseException(response()->json([
                    'message' => 'Unauthorized.'
                ], 403));
            }
        }

        return $this->resource::make($this->object);
    }

    /**
     * Create: for regular users force applicant_id from auth.
     * Prevent duplicate pending application for same course & applicant.
     */
    public function create(BasicRequest $request): mixed
    {
        $in = $request->validated();

        return DB::transaction(function () use ($in, $request) {
            $isAdmin = (bool) $request->user('admin');


            if (! $isAdmin) {
                $applicant = $request->user('user') ?? auth()->user();
                if (! $applicant) {
                    throw new HttpResponseException(response()->json([
                        'message' => 'Unauthenticated.'
                    ], 401));
                }
                $in['applicant_id'] = $applicant->id;
            }


            $in['status'] = 0;


            $existsPending = $this->model::where('course_id', $in['course_id'])
                ->where('applicant_id', $in['applicant_id'])
                ->where(function ($q) {
                    $q->whereNull('reviewed_by')
                    ->orWhere('status', 0);
                })
                ->exists();

            if ($existsPending) {
                throw new HttpResponseException(response()->json([
                    'message' => 'You already have a pending application for this course. Please wait until it is reviewed.'
                ], 422));
            }

            $object = $this->model::create($in);

            return $this->resource::make($object->load($this->relations));
        });
    }


    public function update(BasicRequest $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {
            $object = $this->model::with($this->relations)->findOrFail($request->id);

            
            if (array_key_exists('status', $data)) {
                $newStatus = (int) $data['status'];
                $oldStatus = (int) ($object->status ?? 0);

                if ($newStatus !== $oldStatus) {

                    $data['reviewed_at'] = $newStatus === 0 ? null : now();
                }
            }

            $object->update($data);

            return $this->resource::make($object->fresh()->load($this->relations));
        });
    }
}
