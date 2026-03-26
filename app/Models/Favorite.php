<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends BaseModel
{
    protected $fillable = [
        'user_id' => 'user_id',
        'favoritable_id' => 'favoritable_id',
        'favoritable_type' => 'favoritable_type',
        'note' => 'note',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'favoritable_id' => 'integer',
    ];

     protected $search = ['note'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }

    //
}
