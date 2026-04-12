<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreCourseQuizAttemptRequest extends BasicRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'course_quiz_id' => 'required|integer|exists:course_quizzes,id',
            'score' => 'required|integer',
            'passed' => 'required|boolean',
            'submitted_at' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

}
