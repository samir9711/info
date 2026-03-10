<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Setting extends BaseModel
{protected $fillable = [
        'key' => 'key',
        'value' => 'value',
    ];

protected $casts = [
    ];

    //
}
