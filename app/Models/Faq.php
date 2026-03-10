<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Faq extends BaseModel
{
    protected $fillable = [
        'question' => 'question',
        'response' => 'response',
    ];

    protected $casts = [
        'question' => 'array',
        'response' => 'array',
    ];

    //
}
