<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreCourseQuizAttemptAnswerRequest extends BasicRequest
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
            'quiz_attempt_id' => 'required|integer|exists:quiz_attempts,id',
            'question_id' => 'required|integer|exists:questions,id',
            'answer_id' => 'nullable|integer|exists:answers,id',
            'is_correct' => 'required|boolean',
        ];
    }

}
