<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreCourseQuizRequest extends BasicRequest
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
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'is_final' => ['required', 'boolean'],

            'quiz' => ['required', 'array'],
            'quiz.title' => ['required', 'array'],
            'quiz.description' => ['nullable', 'array'],

            'quiz.questions' => ['required', 'array', 'min:1'],
            'quiz.questions.*.question' => ['required', 'array'],
            'quiz.questions.*.image' => ['nullable', 'string'],
            'quiz.questions.*.answers' => ['required', 'array', 'min:2'],

            'quiz.questions.*.answers.*.answer' => ['required', 'array'],
            'quiz.questions.*.answers.*.is_correct' => ['required', 'boolean'],
        ];
    }

}
