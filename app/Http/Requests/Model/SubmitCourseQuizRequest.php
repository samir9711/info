<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;

class SubmitCourseQuizRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'course_quiz_id' => ['required', 'integer', 'exists:course_quizzes,id'],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'answers.*.answer_id' => ['required', 'integer', 'exists:answers,id'],
        ];
    }
}
