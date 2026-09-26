<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Podcast extends BaseModel
{
    protected $fillable = [
        'title' => 'title',
        'description' => 'description',
        'cover' => 'cover',
        'audio' => 'audio',
        'video' => 'video',
        'duration' => 'duration',
        'instructor_id' => 'instructor_id',
        'category_id' => 'category_id',
        'collection_id' => 'collection_id',
        'views' => 'views',
        'downloads' => 'downloads',
        'hls_disk' => 'hls_disk',
        'hls_path' => 'hls_path',
        'hls_status' => 'hls_status',
        'hls_error' => 'hls_error',
        'hls_processed_at' => 'hls_processed_at',

    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'duration' => 'integer',
        'instructor_id' => 'integer',
        'category_id' => 'integer',
        'views' => 'integer',
        'downloads' => 'integer',
        'hls_processed_at' => 'datetime',
    ];

    protected array $fileAttributes = [
        'cover' => 'single',
        'video' => 'single',
    ];

    protected $search = ['title', 'description'];

    protected array $filterable = [

        'instructor_id'=>'int',
        'category_id'=>'int',
        'collection_id'=>'int',


    ];

    protected array $dynamicFilterColumns = [
        'instructor_id',
        'category_id',
        'collection_id',
    ];


    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function collection()
    {
        return $this->belongsTo(PodcastCollection::class, 'collection_id');
    }

    //
}
