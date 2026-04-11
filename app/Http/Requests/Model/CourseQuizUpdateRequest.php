<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;

class CourseQuizUpdateRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:course_quizzes,id'],
            'course_id' => ['sometimes', 'integer', 'exists:courses,id'],
            'is_final' => ['sometimes', 'boolean'],

            'quiz' => ['sometimes', 'array'],
            'quiz.title' => ['sometimes', 'array'],
            'quiz.description' => ['sometimes', 'array'],

            'quiz.questions' => ['sometimes', 'array', 'min:1'],
            'quiz.questions.*.question' => ['required_with:quiz.questions', 'array'],
            'quiz.questions.*.image' => ['nullable', 'string'],
            'quiz.questions.*.answers' => ['required_with:quiz.questions', 'array', 'min:2'],

            'quiz.questions.*.answers.*.answer' => ['required_with:quiz.questions', 'array'],
            'quiz.questions.*.answers.*.is_correct' => ['required_with:quiz.questions', 'boolean'],
        ];
    }
}
