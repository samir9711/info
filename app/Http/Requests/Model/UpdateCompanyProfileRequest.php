<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;

class UpdateCompanyProfileRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|array',
            'email' => 'sometimes|email|max:255',
            'password' => 'nullable|string|min:8',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'color' => 'nullable|string|max:50',
            'profile_image_path' => 'nullable|string|max:255',
            'profile_video_path' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'about' => 'nullable|array',
            'logo_path' => 'nullable|string|max:255',
        ];
    }
}
