<?php

namespace App\Http\Requests\LessonVideo;

use Illuminate\Foundation\Http\FormRequest;

class RefreshLessonVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('user') !== null;
    }

    public function rules(): array
    {
        return [
            'playback_session_id' => [
                'required',
                'string',
                'size:64',
                'regex:/\A[A-Za-z0-9]{64}\z/',
            ],
        ];
    }
}
