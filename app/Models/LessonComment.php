<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class LessonComment extends BaseModel
{
    protected $fillable = [
        'lesson_id' => 'lesson_id',
        'user_id' => 'user_id',
        'parent_id' => 'parent_id',
        'comment' => 'comment',
    ];

    protected $casts = [
        'lesson_id' => 'integer',
        'user_id' => 'integer',
        'parent_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function parent()
    {
        return $this->belongsTo(LessonComment::class, 'parent_id');
    }

    //
}
