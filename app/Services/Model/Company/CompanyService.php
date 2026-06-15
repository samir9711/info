<?php

namespace App\Services\Model\Company;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Company;
use App\Http\Resources\Model\CompanyResource;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Company::class
        );

        $this->resource = CompanyResource::class;
    }

    public function updateProfile(BasicRequest $request): mixed
    {
        $companyId = auth('company')->id();

        if (!$companyId) {
            throw ValidationException::withMessages([
                'auth' => ['Only company accounts can update their profile.'],
            ]);
        }

        $data = $request->validated();

        // منع أي محاولة لتغيير الهوية
        unset($data['id'], $data['company_id']);

        return DB::transaction(function () use ($companyId, $data) {
            $company = Company::findOrFail($companyId);
            $company->update($data);

            return $this->resource::make($company->fresh());
        });
    }
}
