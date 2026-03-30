<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CourseInstructor extends BaseModel
{
    protected $fillable = [
        'course_id' => 'course_id',
        'instructor_id' => 'instructor_id',
    ];

    protected $casts = [
        'course_id' => 'integer',
        'instructor_id' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    //
}
