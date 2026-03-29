<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class LessonComment extends BaseModel
{
    protected $fillable = [
        'lesson_id' => 'lesson_id',
        'user_id' => 'user_id',
        'admin_id' => 'admin_id',
        'parent_id' => 'parent_id',
        'comment' => 'comment',
    ];

    protected $casts = [
        'lesson_id' => 'integer',
        'user_id' => 'integer',
        'parent_id' => 'integer',
        'admin_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function parent()
    {
        return $this->belongsTo(LessonComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(LessonComment::class, 'parent_id');
    }
    protected array $filterable = [
        'parent_id'=>'int',
        'comment'=>'like',
        'lesson_id'=>'int'
    ];

    protected array $dynamicFilterColumns = [
        'parent_id' ,
        'lesson_id'

    ];

    //
}
