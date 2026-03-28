<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Quiz extends BaseModel
{
    protected $fillable = [
        'title' => 'title',
        'description' => 'description',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function lessonQuizzes()
    {
        return $this->hasMany(LessonQuiz::class);
    }

    public function attempts()
    {
        return $this->hasManyThrough(QuizAttempt::class, LessonQuiz::class, 'quiz_id', 'lesson_quiz_id');
    }

    //
}
