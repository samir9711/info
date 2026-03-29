<?php

namespace App\Models;

use App\Models\BaseModel;

use App\Support\HasTags;
use Illuminate\Database\Eloquent\Model;

class Course extends BaseModel
{
    use HasTags;
    protected $fillable = [
        'category_id' => 'category_id',
        'title' => 'title',
        'subtitle' => 'subtitle',
        'short_description' => 'short_description',
        'description' => 'description',
        'image' => 'image',
        'is_free' => 'is_free',
        'price' => 'price',
        'currency_id' => 'currency_id',
        'publish' => 'publish',
        'published_at' => 'published_at',
        'is_featured' => 'is_featured',
        'level' => 'level',
        'expected_hours' => 'expected_hours',
        'what_will_learn' => 'what_will_learn',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'title' => 'array',
        'subtitle' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'is_free' => 'boolean',
        'price' => 'float',
        'currency_id' => 'integer',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'level' => 'string',
        'expected_hours' => 'integer',
        'what_will_learn' => 'array',
    ];


    protected $search = ['title', 'description'];


    protected array $filterable = [
        'category_id' => 'int',
        'category_tree_id' => [
            'column' => 'category_id',
            'op' => 'tree',
            'type' => 'int',
        ],
        'is_free' => 'bool',
        'is_featured' => 'bool',

    ];

    protected array $treeFilterModels = [
        'category_id' => Category::class,
    ];

    protected array $dynamicFilterColumns = [
        'category_id' ,
        'is_free',
        'is_featured'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    //
}
