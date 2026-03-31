<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreInstructorRequest extends BasicRequest
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
            'name' => 'required|array',
            'image' => 'nullable|string|max:255',
            'profession' => 'nullable|array',
            'bio' => 'nullable|array',
            'headline' => 'nullable|array',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
        ];
    }

}
