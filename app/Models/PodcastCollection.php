<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class PodcastCollection extends BaseModel
{
    protected $fillable = [
        'title' => 'title',
        'description' => 'description',
        'cover' => 'cover',
        'sort_order' => 'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'sort_order' => 'integer',
    ];

    protected array $fileAttributes = [
        'cover' => 'single',

    ];
    protected $search = ['title', 'description'];

    public function podcasts()
    {
        return $this->hasMany(Podcast::class, 'collection_id');
    }

    //
}
