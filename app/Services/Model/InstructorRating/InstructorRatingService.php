<?php

namespace App\Services\Model\InstructorRating;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\InstructorRating;
use App\Http\Resources\Model\InstructorRatingResource;
use App\Http\Requests\Basic\BasicRequest;
use App\Models\Instructor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class InstructorRatingService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = InstructorRating::class
        );

        $this->resource = InstructorRatingResource::class;
        $this->relations = ['user'];
    }

    public function create(BasicRequest $request): mixed
    {
        $data = $request->validated();

        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        return DB::transaction(function () use ($data, $user) {
            $instructor = Instructor::findOrFail($data['instructor_id']);

            $rating = InstructorRating::updateOrCreate(
                [
                    'instructor_id' => $instructor->id,
                    'user_id' => $user->id,
                ],
                [
                    'rating'  => (int) $data['rating'],
                    'comment' => $data['comment'] ?? null,
                ]
            );

            return $this->resource::make(
                $rating->load($this->relations)
            );
        });
    }


    public function delete(Request $request): bool
    {
        $rating = InstructorRating::where('id', $request->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return (bool) $rating->delete();
    }

    public function byInstructor($request): mixed
    {
        $instructorId = (int) $request->input('instructor_id');

        $ratings = InstructorRating::with($this->relations)
            ->where('instructor_id', $instructorId)
            ->latest()
            ->get();

        return $this->resource::collection($ratings);
    }

    public function summary($request): array
    {
        $instructorId = (int) $request->input('instructor_id');

        $query = InstructorRating::where('instructor_id', $instructorId);

        return [
            'instructor_id' => $instructorId,
            'count' => $query->count(),
            'average' => round((float) $query->avg('rating'), 2),
        ];
    }
}
