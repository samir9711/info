<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Instructor extends BaseModel
{
    protected $fillable = [
        'name' => 'name',
        'image' => 'image',
        'profession' => 'profession',
        'bio' => 'bio',
        'headline' => 'headline',
        'email' => 'email',
        'phone' => 'phone',
        'experience' => 'experience',
    ];

    protected $casts = [
        'name' => 'array',
        'profession' => 'array',
        'bio' => 'array',
        'headline' => 'array',
    ];


    public function courses()
    {
        return $this->belongsToMany(Course::class,'course_instructors','instructor_id','course_id'
        )->withTimestamps();
    }


    public function ratings()
    {
        return $this->hasMany(InstructorRating::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    //
}
