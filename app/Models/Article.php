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
        'is_important'=>'is_important',
        'image'=>'image',
    ];

    protected $casts = [
        'title' => 'array',
        'intro' => 'array',
        'conclusion' => 'array',
        'category_id' => 'integer',
        'is_important' => 'boolean'
    ];


    protected $search = ['title', 'description','intro','conclusion'];


    protected array $filterable = [
        'category_id'=>'int',
        'is_important'=>'bool',
    ];

    protected array $dynamicFilterColumns = [
        'category_id' ,
        'is_important'
    ];
    protected array $fileAttributes = [
        'image' => 'single',
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
