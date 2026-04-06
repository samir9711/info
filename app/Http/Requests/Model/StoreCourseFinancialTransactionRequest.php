<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreCourseFinancialTransactionRequest extends BasicRequest
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
            'course_id' => 'required|integer|exists:courses,id',
            'course_application_id' => 'required|integer|exists:course_applications,id',
            'instructor_id' => 'nullable|integer|exists:instructors,id',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'entry_type' => 'required|integer',
            'amount' => 'required|numeric',
            'is_settled' => 'required|boolean',
            'settled_at' => 'nullable|date_format:Y-m-d H:i:s',
            'settled_by' => 'nullable|integer|exists:admins,id',
            'description' => 'nullable|string',
        ];
    }

}
