<?php

namespace App\Services\Model\CompanyJobApplication;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CompanyJobApplication;
use App\Http\Resources\Model\CompanyJobApplicationResource;
use App\Models\CompanyJob;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Http\Request;
use App\Http\Requests\Model\ChangeCompanyJobApplicationStatusRequest;


class CompanyJobApplicationService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CompanyJobApplication::class
        );

        $this->resource = CompanyJobApplicationResource::class;

    }


    public function create(BasicRequest $request): mixed
    {
        $user = auth()->user();

        $job = CompanyJob::findOrFail($request->company_job_id);


        $exists = CompanyJobApplication::where('company_job_id', $job->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'company_job_id' => 'You have already applied for this job.'
            ]);
        }

        $cv = $request->cv;

        if (!$cv) {

            if (!$user->cv) {

                throw ValidationException::withMessages([
                    'cv' => 'Please upload your CV first.'
                ]);

            }

            $cv = $user->cv;
        }

        $this->object = CompanyJobApplication::create([

            'company_job_id' => $job->id,

            'company_id' => $job->company_id,

            'user_id' => $user->id,

            'cover_letter' => $request->cover_letter,

            'cv' => $cv,

            'status' => 'pending',

        ]);

        return $this->resource::make(
            $this->object->load($this->relations)
        );
    }

    public function myApplications(Request $request): mixed
    {
        $user = auth()->user();

        $applications = CompanyJobApplication::query()
            ->with([
                'company',
                'companyJob',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(
                $request->input('per_page', 10),
                ['*'],
                'page',
                $request->input('page', 1)
            );

        return [
            'company_job_applications' => CompanyJobApplicationResource::collection($applications),
            'current_page' => $applications->currentPage(),
            'next_page' => $applications->nextPageUrl(),
            'previous_page' => $applications->previousPageUrl(),
            'total_pages' => $applications->lastPage(),
            'total' => $applications->total(),
        ];
    }

    public function companyApplications(Request $request): mixed
    {
        $company = auth('company')->user(); // أو auth()->user()

        $applications = CompanyJobApplication::query()
            ->with([
                'user',
                'companyJob',
            ])
            ->where('company_id', $company->id)
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(
                $request->input('per_page', 10),
                ['*'],
                'page',
                $request->input('page', 1)
            );

        return [
            'company_job_applications' => CompanyJobApplicationResource::collection($applications),
            'current_page' => $applications->currentPage(),
            'next_page' => $applications->nextPageUrl(),
            'previous_page' => $applications->previousPageUrl(),
            'total_pages' => $applications->lastPage(),
            'total' => $applications->total(),
        ];
    }

    public function changeStatus(ChangeCompanyJobApplicationStatusRequest $request): mixed
    {
        $company = auth('company')->user(); // أو auth()->user()

        $application = CompanyJobApplication::findOrFail($request->id);

        if ($application->company_id != $company->id) {

            throw ValidationException::withMessages([
                'id' => 'This application does not belong to your company.'
            ]);

        }

        // اختياري: منع تعديل الطلب بعد انتهاءه
        if (in_array($application->status, ['accepted', 'rejected'])) {

            throw ValidationException::withMessages([
                'status' => 'This application has already been finalized.'
            ]);

        }

        $application->update([

            'status' => $request->status,

            'company_note' => $request->company_note,

            'reviewed_at' => now(),

        ]);

        return CompanyJobApplicationResource::make(
            $application->fresh([
                'user',
                'companyJob',
            ])
        );
    }
}
