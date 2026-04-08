<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends BaseModel
{
    protected $fillable = [
        'phone' => 'phone',
        'facebook' => 'facebook',
        'instagram' => 'instagram',
        'twitter' => 'twitter',
        'logo' => 'logo',
        'description' => 'description',
    ];

    protected $casts = [
    'description' => 'array',
    ];

    //
}
