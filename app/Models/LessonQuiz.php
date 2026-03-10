<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class LessonQuiz extends BaseModel
{
    protected $fillable = [
        'quiz_id' => 'quiz_id',
        'lesson_id' => 'lesson_id',
        'required' => 'required',
    ];

    protected $casts = [
        'quiz_id' => 'integer',
        'lesson_id' => 'integer',
        'required' => 'boolean',
    ];

    //
}
