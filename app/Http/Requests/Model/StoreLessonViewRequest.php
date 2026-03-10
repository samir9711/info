<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreLessonViewRequest extends BasicRequest
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
            'lesson_id' => 'required|integer|exists:lessons,id',
            'view_count' => 'required|integer',
            'total_watch_seconds' => 'required|integer',
            'last_watched_seconds' => 'nullable|integer',
            'progress_percent' => 'required|integer',
            'is_completed' => 'required|boolean',
            'last_viewed_at' => 'nullable|date_format:Y-m-d H:i:s',
            'device' => 'nullable|string|max:255',
            'ip' => 'nullable|string|max:255',
        ];
    }

}
