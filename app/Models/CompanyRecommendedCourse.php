<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CompanyRecommendedCourse extends BaseModel
{
    protected $fillable = [
        'company_id' => 'company_id',
        'course_id' => 'course_id',
        'note' => 'note',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'course_id' => 'integer',
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    //
}
