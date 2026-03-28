<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends BasicRequest
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
            'course_id' => 'required|integer|exists:courses,id',
            'title' => 'required|array',
            'content' => 'nullable|array',
            'conclusion' => 'nullable|array',
            'video_url' => 'nullable|string|max:255',
            'is_published' => 'required|boolean',
            'free_preview' => 'required|boolean',
            'image' => 'nullable|string|max:255',

            'sources'      => ['nullable', 'array'],
            'sources.*'    => ['string', 'max:255'],


            'quizzes' => ['nullable','array'],
            'quizzes.*.id' => ['nullable','integer','exists:quizzes,id'],
            'quizzes.*.required' => ['nullable','boolean'],

            // quiz fields
            'quizzes.*.title' => ['nullable','array'],
            'quizzes.*.title.ar' => ['nullable','string'],
            'quizzes.*.title.en' => ['nullable','string'],
            'quizzes.*.description' => ['nullable','array'],

            // questions
            'quizzes.*.questions' => ['nullable','array'],
            'quizzes.*.questions.*.id' => ['nullable','integer','exists:questions,id'],
            'quizzes.*.questions.*.question' => ['nullable','array'],
            'quizzes.*.questions.*.question.ar' => ['nullable','string'],
            'quizzes.*.questions.*.question.en' => ['nullable','string'],
            'quizzes.*.questions.*.image' => ['nullable','string'],

            // answers
            'quizzes.*.questions.*.answers' => ['nullable','array'],
            'quizzes.*.questions.*.answers.*.id' => ['nullable','integer','exists:answers,id'],
            'quizzes.*.questions.*.answers.*.answer' => ['nullable','array'],
            'quizzes.*.questions.*.answers.*.is_correct' => ['nullable','boolean'],
        ];
    }

}
