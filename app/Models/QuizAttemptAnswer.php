<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class QuizAttemptAnswer extends BaseModel
{
    protected $fillable = [
        'quiz_attempt_id'=>'quiz_attempt_id',
        'question_id'=>'question_id',
        'answer_id'=>'answer_id',
        'is_correct'=>'is_correct',
    ];

    protected $casts = [
        'quiz_attempt_id' => 'integer',
        'question_id' => 'integer',
        'answer_id' => 'integer',
        'is_correct' => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }
}
