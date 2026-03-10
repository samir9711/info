<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreArticleSectionRequest extends BasicRequest
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
            'article_id' => 'required|integer|exists:articles,id',
            'title' => 'nullable|array',
            'body' => 'nullable|array',
            'conclusion' => 'nullable|array',
            'image' => 'nullable|string|max:255',
        ];
    }

}
