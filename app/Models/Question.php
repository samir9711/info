<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Question extends BaseModel
{
    protected $fillable = [
        'quiz_id' => 'quiz_id',
        'question' => 'question',
        'image' => 'image',
    ];

    protected $casts = [
        'quiz_id' => 'integer',
        'question' => 'array',
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    //
}
