<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;

class LessonQuizPreviewRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
        ];
    }
}
