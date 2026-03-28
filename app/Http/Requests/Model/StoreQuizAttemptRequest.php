<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreQuizAttemptRequest extends BasicRequest
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
            'lesson_quiz_id'        => ['required', 'integer', 'exists:lesson_quizzes,id'],
            'answers'               => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'answers.*.answer_id'   => ['required', 'integer', 'exists:answers,id'],
        ];
    }

}
