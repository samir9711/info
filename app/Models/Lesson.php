<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Lesson extends BaseModel
{
    protected $fillable = [
        'course_id' => 'course_id',
        'title' => 'title',
        'content' => 'content',
        'conclusion' => 'conclusion',
        'video_url' => 'video_url',
        'is_published' => 'is_published',
        'free_preview' => 'free_preview',
    ];

    protected $casts = [
        'course_id' => 'integer',
        'title' => 'array',
        'content' => 'array',
        'conclusion' => 'array',
        'is_published' => 'boolean',
        'free_preview' => 'boolean',
    ];


    protected array $fileAttributes = [
        'video_url' => 'single',
    ];
    protected string $fileDisk = 'private';


    protected $search = ['title', 'content','conclusion'];


    protected array $filterable = [

        'course_id'=>'int',
        'free_preview'=>'bool',
        'is_published'=>'bool',

    ];

    protected array $dynamicFilterColumns = [
        'course_id' ,
        'free_preview' ,
        'is_published' ,
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'lesson_quizzes', 'lesson_id', 'quiz_id')
                    ->withPivot('required')
                    ->withTimestamps();
    }

    public function lessonQuizzes()
    {
        return $this->hasMany(LessonQuiz::class);
    }

    //
}
