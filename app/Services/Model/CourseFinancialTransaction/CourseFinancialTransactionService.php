<?php

namespace App\Services\Model\CourseFinancialTransaction;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CourseFinancialTransaction;
use App\Http\Resources\Model\CourseFinancialTransactionResource;
use App\Http\Requests\Basic\BasicRequest;
use App\Models\CourseApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CourseFinancialTransactionService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CourseFinancialTransaction::class
        );

        $this->resource = CourseFinancialTransactionResource::class;
        $this->relations = ['course', 'application', 'instructor'];
    }

    protected function allQuery(): object
    {
        $request = request();
        $query = $this->model::with($this->relations)->orderByDesc('created_at');

        $admin = $request->user('admin');
        $instructor = $request->user('instructor');

        if (!$admin) {
            if (!$instructor) {
                $query->whereRaw('0 = 1');
            } else {
                $query->where('instructor_id', $instructor->id);
            }
        }

        return $query;
    }

    public function createForApplication(CourseApplication $application): void
    {
        $application->loadMissing('course');

        $course = $application->course;
        if (!$course) {
            return;
        }

        $exists = $this->model::where('course_application_id', $application->id)->exists();
        if ($exists) {
            return;
        }

        $price = (float) $course->price;
        $currencyId = $course->currency_id;
        $instructorId = $course->created_by;

        if ((bool) $course->is_platform_owned) {
            $this->model::create([
                'course_id' => $course->id,
                'course_application_id' => $application->id,
                'instructor_id' => null,
                'currency_id' => $currencyId,
                'entry_type' => 1,
                'amount' => $price,
                'is_settled' => true,
                'settled_at' => now(),
                'description' => 'Platform owned course income',
            ]);

            return;
        }

        $profitPercentage = (float) $course->profit_percentage;
        $payout = round($price - ($price * $profitPercentage / 100), 2);

        $this->model::create([
            'course_id' => $course->id,
            'course_application_id' => $application->id,
            'instructor_id' => null,
            'currency_id' => $currencyId,
            'entry_type' => 1,
            'amount' => $price,
            'is_settled' => true,
            'settled_at' => now(),
            'description' => 'Course income for platform',
        ]);

        $this->model::create([
            'course_id' => $course->id,
            'course_application_id' => $application->id,
            'instructor_id' => $instructorId,
            'currency_id' => $currencyId,
            'entry_type' => 2,
            'amount' => $payout,
            'is_settled' => false,
            'settled_at' => null,
            'description' => 'Instructor payout',
        ]);
    }

    public function myInstructorTransactions(Request $request): mixed
    {
        $instructor = $request->user('instructor');

        if (!$instructor) {
            abort(401);
        }

        $data = $this->model::with($this->relations)
            ->where('instructor_id', $instructor->id)
            ->orderByDesc('created_at')
            ->get();

        return $this->resource::collection($data);
    }

    public function settle(Request $request): mixed
    {
        $admin = $request->user('admin');
        if (!$admin) {
            abort(403, 'Only admin can settle transactions.');
        }

        $request->validate([
            'id' => ['required', 'integer'],
        ]);

        return DB::transaction(function () use ($request, $admin) {
            $transaction = $this->model::findOrFail($request->id);

            if ((int) $transaction->entry_type !== 2) {
                abort(422, 'Only instructor payout entries can be settled.');
            }

            if ($transaction->is_settled) {
                return $this->resource::make($transaction->fresh()->load($this->relations));
            }

            $transaction->update([
                'is_settled' => true,
                'settled_at' => now(),
                'settled_by' => $admin->id,
            ]);

            return $this->resource::make($transaction->fresh()->load($this->relations));
        });
    }
}
