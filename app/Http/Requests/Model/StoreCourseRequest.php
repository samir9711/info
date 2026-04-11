<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends BasicRequest
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
            'category_id' => 'nullable|integer|exists:categories,id',
            'title' => 'required|array',
            'subtitle' => 'nullable|array',
            'short_description' => 'nullable|array',
            'description' => 'nullable|array',
            'image' => 'nullable|string|max:255',
            'is_free' => 'required|boolean',
            'price' => 'nullable|numeric',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'publish' => 'nullable|in:draft,published,archived',
            'published_at' => 'nullable|date_format:Y-m-d H:i:s',
            'is_featured' => 'nullable|boolean',
            'level'=>'nullable|in:beginner,intermediate,advanced',
            'expected_hours'=>'nullable|integer',
            'video_intro'=>'nullable|string',

            'what_will_learn' => ['nullable', 'array'],



            'instructor_ids' => ['nullable', 'array'],
            'instructor_ids.*' => ['integer', 'exists:instructors,id'],


            'tags' => 'nullable|array',
            'tags.*' => 'nullable',
            'tags.*.id' => 'nullable|integer|exists:tags,id',
            'tags.*.name' => 'nullable',
        ];
    }

}
