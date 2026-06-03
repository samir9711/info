<?php

namespace App\Http\Requests\LessonVideo;

use Illuminate\Foundation\Http\FormRequest;

class RefreshLessonVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'playback_session_id' => ['required', 'string'],
        ];
    }
}