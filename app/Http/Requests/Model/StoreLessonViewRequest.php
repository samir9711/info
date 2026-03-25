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
    public function rules()
    {
        return [
            'lesson_id' => ['required','integer','exists:lessons,id'],
            // الموضع الحالي في الفيديو بالثواني (مطلوب لتتبع delta)
            'last_watched_seconds' => ['nullable','integer','min:0'],
            // الطول الكلي للدرس إن كان متاحاً (بالثواني)
            'lesson_duration' => ['nullable','integer','min:1'],
            'device' => ['nullable','string','max:200'],
            'ip' => ['nullable','ip'],
            'completed' => ['nullable','boolean'],
            // لو أردت التحكم بزيادة view_count: client يرسل true/false
            'count_as_new_view' => ['nullable','boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        // بعض الـ clients قد يرسلون strings 'true'/'false'
        if ($this->has('completed')) {
            $this->merge(['completed' => filter_var($this->input('completed'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)]);
        }
        if ($this->has('count_as_new_view')) {
            $this->merge(['count_as_new_view' => filter_var($this->input('count_as_new_view'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)]);
        }
    }

}
