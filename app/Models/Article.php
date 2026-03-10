<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Article extends BaseModel
{
    protected $fillable = [
        'title' => 'title',
        'intro' => 'intro',
        'conclusion' => 'conclusion',
        'category_id'=>'category_id',
    ];

    protected $casts = [
        'title' => 'array',
        'intro' => 'array',
        'conclusion' => 'array',
        'category_id' => 'integer',
    ];


    protected $search = ['title', 'description','intro','conclusion'];


    protected array $filterable = [
        'category_id'=>'int'
    ];

    protected array $dynamicFilterColumns = [
        'category_id' ,
    ];

    public function sections()
    {
        return $this->hasMany(ArticleSection::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    //
}
