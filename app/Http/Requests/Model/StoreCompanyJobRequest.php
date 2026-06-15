<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyJobRequest extends BasicRequest
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
           // 'company_id' => 'required|integer|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'employment_type' => 'required|in:full_time,part_time,contract,internship,remote',
            'status' => 'required|in:draft,open,closed',
            'salary_min' => 'nullable|integer',
            'salary_max' => 'nullable|integer',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'expires_at' => 'nullable|date',
        ];
    }

}
