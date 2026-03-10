<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends BasicRequest
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
            'title' => ['required','array'],
            'title.ar' => ['required','string'],
            'title.en' => ['nullable','string'],

            'intro' => ['nullable','array'],
            'intro.ar' => ['nullable','string'],
            'intro.en' => ['nullable','string'],

            'conclusion' => ['nullable','array'],
            'conclusion.ar' => ['nullable','string'],
            'conclusion.en' => ['nullable','string'],

            'category_id' => ['nullable','integer', Rule::exists('categories', 'id')],

            'sections' => ['nullable','array'],
            'sections.*.id' => ['nullable','integer', Rule::exists('article_sections', 'id')], // <-- added
            'sections.*.title' => ['nullable','array'],
            'sections.*.title.ar' => ['nullable','string'],
            'sections.*.title.en' => ['nullable','string'],
            'sections.*.body' => ['nullable','array'],
            'sections.*.body.ar' => ['nullable','string'],
            'sections.*.body.en' => ['nullable','string'],
            'sections.*.conclusion' => ['nullable','array'],
            'sections.*.conclusion.ar' => ['nullable','string'],
            'sections.*.conclusion.en' => ['nullable','string'],


            // image as string path
            'sections.*.image' => ['nullable','string'],
            'sections.*.remove_image' => ['nullable','boolean'], // <-- added
        ];
    }


    public function messages(): array
    {
        return [

            'title.required' => __('validation.custom.title.required'),
            'title.array'    => __('validation.custom.title.array'),

            'sections.array' => __('validation.custom.sections.array'),
            'sections.*.image.string' => __('validation.custom.sections.image.string'),
        ];
    }

}
