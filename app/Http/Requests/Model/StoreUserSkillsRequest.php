<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;

class StoreUserSkillsRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'skills' => 'required|array|min:1',
            'skills.*.skill_id' => 'required|integer|exists:skills,id',
            'skills.*.note' => 'nullable|string|max:255',
        ];
    }
}
