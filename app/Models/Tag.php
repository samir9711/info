<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Tag extends BaseModel
{
    protected $fillable = [
        'name' => 'name',
        'description' => 'description',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

    //
}
