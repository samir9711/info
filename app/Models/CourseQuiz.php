<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CourseQuiz extends BaseModel
{
    protected $fillable = [
        'quiz_id' => 'quiz_id',
        'course_id' => 'course_id',
        'is_final' => 'is_final',
    ];

    protected $casts = [
        'quiz_id' => 'integer',
        'course_id' => 'integer',
        'is_final' => 'boolean',
    ];

    protected array $filterable = [
        'quiz_id'=>'int',
        'course_id'=>'int',
    ];

    protected array $dynamicFilterColumns = [
        'quiz_id',
        'course_id',
    ];


    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    //
}
