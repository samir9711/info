<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Favorite extends BaseModel
{protected $fillable = [
        'user_id' => 'user_id',
        'favoritable_id' => 'favoritable_id',
        'favoritable_type' => 'favoritable_type',
        'note' => 'note',
    ];

protected $casts = [
        'user_id' => 'integer',
        'favoritable_id' => 'integer',
    ];

    //
}
