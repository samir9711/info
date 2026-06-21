<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;

class DeleteUserSkillsRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'ids' => 'nullable|array|min:1',
            'ids.*' => 'integer|exists:user_skills,id',

            'skill_ids' => 'nullable|array|min:1',
            'skill_ids.*' => 'integer|exists:skills,id',
        ];
    }
}
