<?php

namespace App\Http\Controllers\User;

use App\Facades\Services\User\UserFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreUserRequest;
use App\Http\Requests\Model\UpdateUserProfileRequest;
use Illuminate\Http\Request;

class UserController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "user";
        $this->service = UserFacade::class;
        $this->createRequest = StoreUserRequest::class;
        $this->updateRequest = StoreUserRequest::class;
    }

    public function updateMyProfile(UpdateUserProfileRequest $request)
    {
        try {
            return $this->apiResponse(
                $this->service::updateMyProfile($request)
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
