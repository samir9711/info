<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Certificate extends BaseModel
{
    protected $fillable = [
        'user_id' => 'user_id',
        'course_id' => 'course_id',
        'meta' => 'meta',
        'certificate_number' => 'certificate_number',
        'score_percent' => 'score_percent',
        'passing_mark' => 'passing_mark',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_id' => 'integer',
        'meta' => 'array',
        'score_percent' => 'integer',
        'passing_mark' => 'integer',
    ];

    //
}
