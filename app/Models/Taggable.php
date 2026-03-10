<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Taggable extends BaseModel
{
    protected $fillable = [
        'tag_id' => 'tag_id',
        'taggable_id' => 'taggable_id',
        'taggable_type' => 'taggable_type',
    ];

    protected $casts = [
        'tag_id' => 'integer',
        'taggable_id' => 'integer',
    ];

    //
}
