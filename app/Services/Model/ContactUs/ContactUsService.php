<?php

namespace App\Services\Model\ContactUs;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\ContactUs;
use App\Http\Resources\Model\ContactUsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactUsService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = ContactUs::class
        );

        $this->resource = ContactUsResource::class;
    }


    public function show(Request $request): mixed {


        $this->object = $this->model::with(
            $this->relations
        )->withTrashed()->withCount($this->countRelations)->firstOrFail();

        return $this->resource::make($this->object);

    }

    public function create(Request $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $item = $this->model::withTrashed()->first();
            if ($item) {
                $item->update($data);
            } else {
                $item = $this->model::create($data);
            }

            $this->object = $item->fresh()->load($this->relations);

            return $this->resource::make($this->object);
        });
    }
}
