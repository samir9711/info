<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaUpload extends BaseModel
{
    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'model_id',
        'model_type',
        'tus_id',
        'original_name',
        'mime_type',
        'size',
        'uploaded_size',
        'path',
        'status',
        'error',
        'started_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'model_id' => 'integer',
        'size' => 'integer',
        'uploaded_size' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploadable(): MorphTo
    {
        return $this->morphTo();
    }
}
