<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class EventVideo extends BaseModel
{
    protected $fillable = [
        'event_id' => 'event_id',
        'title' => 'title',
        'description' => 'description',
        'thumbnail' => 'thumbnail',
        'video' => 'video',
        'duration' => 'duration',
        'views' => 'views',
    ];

    protected $casts = [
        'event_id' => 'integer',
        'title' => 'array',
        'description' => 'array',
        'duration' => 'integer',
        'views' => 'integer',
    ];


    protected array $filterable = [

        'event_id'=>'int',


    ];

    protected array $dynamicFilterColumns = [

        'event_id'
    ];

    protected $search = ['title', 'description'];

    protected array $fileAttributes = [
       'thumbnail' => 'single',
        'video' => 'single',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    //
}
