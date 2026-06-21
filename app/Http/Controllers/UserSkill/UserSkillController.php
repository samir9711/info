<?php

namespace App\Http\Controllers\UserSkill;

use App\Facades\Services\UserSkill\UserSkillFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\DeleteUserSkillsRequest;
use App\Http\Requests\Model\StoreUserSkillRequest;
use App\Http\Requests\Model\StoreUserSkillsRequest;
use Illuminate\Http\Request;

class UserSkillController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "user_skill";
        $this->service = UserSkillFacade::class;
        $this->createRequest = StoreUserSkillRequest::class;
        $this->updateRequest = StoreUserSkillRequest::class;
    }

    public function storeMany(StoreUserSkillsRequest $request)
    {
        try {
            return $this->apiResponse(
                $this->service::storeMany($request)
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyMany(DeleteUserSkillsRequest $request)
    {
        try {
            return $this->apiResponse(
                $this->service::destroyMany($request)
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function mySkills()
    {
        try {
            return $this->apiResponse(
                $this->service::getMySkills()
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
