<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;

class UpdateUserProfileRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'father_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'sometimes|string|max:255',
            'city' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'residence' => 'nullable|string|max:255',
            'email' => 'sometimes|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'cv' => 'nullable|string|max:255',
        ];
    }
}
