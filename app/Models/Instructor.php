<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Instructor extends BaseAuthModel
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
        'password' => 'password',
    ];

    protected $casts = [
        'name' => 'array',
        'profession' => 'array',
        'bio' => 'array',
        'headline' => 'array',
        'experience' => 'array',
    ];

    protected $hidden = [
        'password',

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


    public function setPasswordAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['password'] = $value;
            return;
        }

        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    //
}
