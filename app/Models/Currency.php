<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Currency extends BaseModel
{protected $fillable = [
        'code' => 'code',
        'name' => 'name',
        'symbol' => 'symbol',
    ];

protected $casts = [
        'name' => 'array',
    ];

    //
}
