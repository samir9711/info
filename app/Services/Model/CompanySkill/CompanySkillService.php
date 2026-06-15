<?php

namespace App\Services\Model\CompanySkill;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CompanySkill;
use App\Http\Resources\Model\CompanySkillResource;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanySkillService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CompanySkill::class
        );

        $this->resource = CompanySkillResource::class;
        $this->relations = ['company'];
    }

    protected function companyId(): int
    {
        $companyId = auth('company')->id();

        if (!$companyId) {
            throw ValidationException::withMessages([
                'auth' => ['Only company accounts can perform this action.'],
            ]);
        }

        return (int) $companyId;
    }

    protected function allQuery(): object
    {
        return $this->model::withFilters()
            ->where('company_id', $this->companyId())
            ->with($this->relations)
            ->orderBy('created_at', 'desc');
    }

    public function create(BasicRequest $request): mixed
    {
        $data = $request->validated();
        $data['company_id'] = $this->companyId();

        return DB::transaction(function () use ($data) {
            $this->object = $this->model::create($data);

            return $this->resource::make($this->object->load($this->relations));
        });
    }

    public function show(Request $request): mixed
    {
        $this->object = $this->model::with($this->relations)
            ->where('company_id', $this->companyId())
            ->withCount($this->countRelations)
            ->findOrFail($request->id);

        return $this->resource::make($this->object);
    }

    public function update(BasicRequest $request): mixed
    {
        $this->object = $this->model::where('company_id', $this->companyId())
            ->findOrFail($request->id);

        return DB::transaction(function () use ($request) {
            $this->object->update($request->validated());

            return $this->resource::make($this->object->fresh($this->relations));
        });
    }

    public function deactivate(Request $request): bool
    {
        $this->object = $this->model::where('company_id', $this->companyId())
            ->findOrFail($request->id);

        return $this->object->delete();
    }

    public function destroy(Request $request): bool
    {
        $this->object = $this->model::where('company_id', $this->companyId())
            ->findOrFail($request->id);

        return $this->object->forceDelete();
    }

    public function activate(Request $request): bool
    {
        $this->object = $this->model::withTrashed()
            ->where('company_id', $this->companyId())
            ->findOrFail($request->id);

        return $this->object->restore();
    }
}
