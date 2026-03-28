<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends BaseModel
{
    protected $fillable = [
        'user_id'=>'user_id',
        'lesson_quiz_id'=>'lesson_quiz_id',
        'score'=>'score',
        'correct_count'=>'correct_count',
        'wrong_count'=>'wrong_count',
        'started_at'=>'started_at',
        'submitted_at'=>'submitted_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'lesson_quiz_id' => 'integer',
        'score' => 'float',
        'correct_count' => 'integer',
        'wrong_count' => 'integer',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lessonQuiz()
    {
        return $this->belongsTo(LessonQuiz::class);
    }

    public function answers()
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }
}
