<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CourseApplication extends BaseModel
{
    protected $fillable = [
        'course_id' => 'course_id',
        'applicant_id' => 'applicant_id',
        'message' => 'message',
        'status' => 'status',
        'reviewed_by' => 'reviewed_by',
        'reviewed_at' => 'reviewed_at',
        'image' => 'image',
    ];

    protected $casts = [
        'course_id' => 'integer',
        'applicant_id' => 'integer',
        'status' => 'integer',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    protected array $fileAttributes = [
        'image' => 'single',
    ];


    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function applicant()
    {
        return $this->belongsTo(User::class);
    }

    //
}
