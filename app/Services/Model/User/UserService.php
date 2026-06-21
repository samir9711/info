<?php

namespace App\Services\Model\User;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\User;
use App\Http\Resources\Model\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = User::class
        );

        $this->resource = UserResource::class;
        $this->relations = [];
    }

    public function create(BasicRequest $request): mixed
    {
        $data = $request->validated();


        $data['email_status'] = $data['email_status'] ?? true;
        $data['activated_at'] = $data['activated_at'] ?? now();
        $data['status'] = $data['status'] ?? true;
        $data['email_verified_at'] = $data['email_verified_at'] ?? now();
        $data['otp_delivery_method'] = $data['otp_delivery_method'] ?? 'email';


        if (isset($data['password']) && $data['password'] !== null) {
            $data['password'] = Hash::make($data['password']);
        }


        $this->object = $this->model::create($data);
        $this->object->load($this->relations);


        return $this->resource::make($this->object);
    }

    public function updateMyProfile(BasicRequest $request): mixed
    {
        $user = auth('user')->user();

        if (!$user) {
            throw ValidationException::withMessages([
                'auth' => ['Only user accounts can update their profile.'],
            ]);
        }

        $data = $request->validated();

        unset(
            $data['id'],
            $data['status'],
            $data['email_status'],
            $data['activated_at'],
            $data['email_verified_at'],
            $data['otp_delivery_method']
        );

        return DB::transaction(function () use ($user, $data) {
            $user->update($data);
            $user->refresh();

            return $this->resource::make($user);
        });
    }
}
