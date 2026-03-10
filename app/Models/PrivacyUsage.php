<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class PrivacyUsage extends BaseModel
{protected $fillable = [
        'description' => 'description',
    ];

protected $casts = [
        'description' => 'array',
    ];

    //
}
