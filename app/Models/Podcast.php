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
        'views' => 'views',
        'downloads' => 'downloads',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'duration' => 'integer',
        'instructor_id' => 'integer',
        'category_id' => 'integer',
        'views' => 'integer',
        'downloads' => 'integer',
    ];

    protected array $fileAttributes = [
        'cover' => 'single',
        'video' => 'single',
    ];

    protected $search = ['title', 'description'];

    protected array $filterable = [

        'instructor_id'=>'int',
        'category_id'=>'int'


    ];

    protected array $dynamicFilterColumns = [
        'instructor_id',
        'category_id'
    ];


    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //
}
