<?php

namespace App\Http\Controllers\Skill;

use App\Facades\Services\Skill\SkillFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreSkillRequest;
use Illuminate\Http\Request;

class SkillController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "skill";
        $this->service = SkillFacade::class;
        $this->createRequest = StoreSkillRequest::class;
        $this->updateRequest = StoreSkillRequest::class;
    }
}