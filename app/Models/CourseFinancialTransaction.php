<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CourseFinancialTransaction extends BaseModel
{
    protected $fillable = [
        'course_id' => 'course_id',
        'course_application_id' => 'course_application_id',
        'instructor_id' => 'instructor_id',
        'currency_id' => 'currency_id',
        'entry_type' => 'entry_type',
        'amount' => 'amount',
        'is_settled' => 'is_settled',
        'settled_at' => 'settled_at',
        'settled_by' => 'settled_by',
        'description' => 'description',
    ];

    protected $casts = [
        'course_id' => 'integer',
        'course_application_id' => 'integer',
        'instructor_id' => 'integer',
        'currency_id' => 'integer',
        'entry_type' => 'integer',
        'amount' => 'float',
        'is_settled' => 'boolean',
        'settled_at' => 'datetime',
        'settled_by' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function application()
    {
        return $this->belongsTo(CourseApplication::class, 'course_application_id');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    //
}
