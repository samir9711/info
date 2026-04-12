<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CourseQuizAttempt extends BaseModel
{
    protected $fillable = [
        'user_id' => 'user_id',
        'course_quiz_id' => 'course_quiz_id',
        'score' => 'score',
        'passed' => 'passed',
        'submitted_at' => 'submitted_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_quiz_id' => 'integer',
        'score' => 'integer',
        'passed' => 'boolean',
        'submitted_at' => 'datetime',
    ];


     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courseQuiz()
    {
        return $this->belongsTo(CourseQuiz::class);
    }

    public function answers()
    {
        return $this->hasMany(CourseQuizAttemptAnswer::class, 'course_quiz_attempt_id');
    }
    //
}
