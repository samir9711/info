<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Category extends BaseModel
{
    protected $fillable = [
        'parent_id' => 'parent_id',
        'name' => 'name',
        'description' => 'description',
        'image' => 'image',
        'points' => 'points',
        'title' => 'title',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'name' => 'array',
        'description' => 'array',
        'points' => 'array',
        'title' => 'array',
    ];


    protected $search = ['name', 'description'];


    protected array $filterable = [
        'parent_id'=>'int'
    ];

    protected array $dynamicFilterColumns = [
        'parent_id' ,
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }


    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id');
    }


    //
}
