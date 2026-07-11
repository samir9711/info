<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Event extends BaseModel
{
    protected $fillable = [
        'title' => 'title',
        'description' => 'description',
        'cover' => 'cover',
        'views' => 'views',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'views' => 'integer',
    ];

    protected $search = ['title', 'description'];

    //
}
