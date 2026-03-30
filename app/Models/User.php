<?php

namespace App\Models;

use App\Models\BaseAuthModel;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends BaseAuthModel
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'father_name' => 'father_name',
        'description' => 'description',
        'phone' => 'phone',
        'city' => 'city',
        'image' => 'image',
        'gender' => 'gender',
        'birth_date' => 'birth_date',
        'residence' => 'residence',
        'email' => 'email',
        'email_verified_at' => 'email_verified_at',
        'email_status' => 'email_status',
        'activated_at' => 'activated_at',
        'password' => 'password',
        'status' => 'status',
        'otp_delivery_method' => 'otp_delivery_method',
        'remember_token' => 'remember_token',
    ];

    protected $casts = [
        'birth_date' => 'datetime',
        'email_verified_at' => 'datetime',
        'email_status' => 'boolean',
        'activated_at' => 'datetime',
        'status' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $search = ['first_name', 'last_name', 'email', 'phone'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function instructorRatings()
    {
        return $this->hasMany(InstructorRating::class);
    }
}
