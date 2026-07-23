<?php

namespace App\Http\Requests\LessonVideo;

use Illuminate\Foundation\Http\FormRequest;

class StreamLessonVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('user') !== null;
    }

    public function rules(): array
    {
        return [];
    }
}
