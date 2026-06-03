<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Slide extends BaseModel
{protected $fillable = [
        'name' => 'name',
        'description' => 'description',
        'image' => 'image',
        'link' => 'link',
    ];

protected $casts = [
    ];

    //
}
