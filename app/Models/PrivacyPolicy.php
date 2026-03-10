<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends BaseModel
{protected $fillable = [
        'description' => 'description',
    ];

protected $casts = [
        'description' => 'array',
    ];

    //
}
