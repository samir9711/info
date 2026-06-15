<?php

namespace App\Services\Model\CompanyContactInfo;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CompanyContactInfo;
use App\Http\Resources\Model\CompanyContactInfoResource;
use App\Http\Resources\Basic\BasicResource;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Support\Arr;

use Illuminate\Support\Facades\DB;

class CompanyContactInfoService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CompanyContactInfo::class
        );

        $this->resource = CompanyContactInfoResource::class;
    }

     public function create(BasicRequest $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            if (auth('company')->check()) {
                $companyId = auth('company')->id();

                $object = CompanyContactInfo::updateOrCreate(
                    ['company_id' => $companyId],
                    Arr::except($data, ['company_id']) + ['company_id' => $companyId]
                );

                return $this->resource::make($object->fresh());
            }

            if (auth('admin')->check()) {
                if (empty($data['company_id'])) {
                    throw ValidationException::withMessages([
                        'company_id' => ['company_id is required for admin.'],
                    ]);
                }

                $object = CompanyContactInfo::updateOrCreate(
                    ['company_id' => (int) $data['company_id']],
                    $data
                );

                return $this->resource::make($object->fresh());
            }

            throw ValidationException::withMessages([
                'auth' => ['Unauthorized guard.'],
            ]);
        });
    }

    public function show(Request $request): mixed
    {
        $query = CompanyContactInfo::with($this->relations);

        if (auth('company')->check()) {
            $companyId = auth('company')->id();

            $object = $query->where('company_id', $companyId)->firstOrFail();

            return $this->resource::make($object);
        }

        if (auth('admin')->check()) {
            $validated = validator($request->all(), [
                'company_id' => 'required|integer|exists:companies,id',
            ])->validate();

            $object = $query->where('company_id', $validated['company_id'])->firstOrFail();

            return $this->resource::make($object);
        }

        throw ValidationException::withMessages([
            'auth' => ['Unauthorized guard.'],
        ]);
    }
}
