<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class LessonView extends BaseModel
{
    protected $fillable = [
        'user_id' => 'user_id',
        'lesson_id' => 'lesson_id',
        'view_count' => 'view_count',
        'total_watch_seconds' => 'total_watch_seconds',
        'last_watched_seconds' => 'last_watched_seconds',
        'progress_percent' => 'progress_percent',
        'is_completed' => 'is_completed',
        'last_viewed_at' => 'last_viewed_at',
        'device' => 'device',
        'ip' => 'ip',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'lesson_id' => 'integer',
        'view_count' => 'integer',
        'total_watch_seconds' => 'integer',
        'last_watched_seconds' => 'integer',
        'progress_percent' => 'integer',
        'is_completed' => 'boolean',
        'last_viewed_at' => 'datetime',
    ];
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //
}
