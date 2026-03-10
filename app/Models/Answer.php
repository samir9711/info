<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Answer extends BaseModel
{
    protected $fillable = [
        'question_id' => 'question_id',
        'answer' => 'answer',
        'is_correct' => 'is_correct',
    ];

    protected $casts = [
        'question_id' => 'integer',
        'is_correct' => 'boolean',
        'answer'    => 'array',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    //
}
