<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends BasicRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [


            'name' => 'sometimes|array',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('companies', 'email')->ignore($this->id),
            ],
            'password' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'color' => 'nullable|string|max:20',
            'profile_image_path' => 'nullable|string|max:255',
            'profile_video_path' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'about' => 'nullable|array',
            'logo_path' => 'nullable|string|max:255',
        ];
    }

}
