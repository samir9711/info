<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Company extends BaseAuthModel
{
    protected $fillable = [
        'name' => 'name',
        'email' => 'email',
        'password' => 'password',
        'address' => 'address',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
        'color' => 'color',
        'profile_image_path' => 'profile_image_path',
        'profile_video_path' => 'profile_video_path',
        'description' => 'description',
        'about' => 'about',
        'logo_path' => 'logo_path',
    ];

    protected $casts = [
        'name' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'description' => 'array',
        'about' => 'array',
    ];

    protected $hidden = [
        'password',

    ];


    public function sections()
    {
        return $this->hasMany(CompanySection::class);
    }

    public function skills()
    {
        return $this->hasMany(CompanySkill::class);
    }

    public function recommendedCourses()
    {
        return $this->hasMany(CompanyRecommendedCourse::class);
    }

    public function contactInfo()
    {
        return $this->hasOne(CompanyContactInfo::class);
    }

    public function galleryImages()
    {
        return $this->hasMany(CompanyGalleryImage::class);
    }

    public function jobs()
    {
        return $this->hasMany(CompanyJob::class);
    }

    public function setPasswordAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['password'] = Hash::make($value);
    }

    //
}
