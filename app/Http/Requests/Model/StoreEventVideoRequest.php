<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreEventVideoRequest extends BasicRequest
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
            'event_id' => 'required|integer|exists:events,id',
            'title' => 'required|array',
            'description' => 'nullable|array',
            'thumbnail' => 'nullable|string|max:255',
            'video' => 'required|string|max:255',
            'duration' => 'nullable|integer',
            //'views' => 'required|integer',
        ];
    }

}
