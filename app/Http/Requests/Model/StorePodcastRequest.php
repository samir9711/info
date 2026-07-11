<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StorePodcastRequest extends BasicRequest
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
            'title' => 'required|array',
            'description' => 'nullable|array',
            'cover' => 'nullable|string|max:255',
            'audio' => 'nullable|string|max:255',
            'video' => 'nullable|string|max:255',
            'duration' => 'nullable|integer',
            'instructor_id' => 'nullable|integer|exists:instructors,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            //'views' => 'required|integer',
            //'downloads' => 'required|integer',
        ];
    }

}
