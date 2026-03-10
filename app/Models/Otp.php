<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Otp extends BaseModel
{protected $fillable = [
        'verifiable_type' => 'verifiable_type',
        'verifiable_id' => 'verifiable_id',
        'otp' => 'otp',
        'purpose' => 'purpose',
        'channel' => 'channel',
        'is_used' => 'is_used',
        'expires_at' => 'expires_at',
        'verified_at' => 'verified_at',
    ];

protected $casts = [
        'verifiable_id' => 'integer',
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    //
}
