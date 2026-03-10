<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreCourseApplicationRequest extends BasicRequest
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
            'applicant_id' => 'nullable|integer|exists:users,id',
            'message' => 'nullable|string',
            'status' => 'required|integer',
            'reviewed_by' => 'nullable|integer|exists:users,id',
            'reviewed_at' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

}
