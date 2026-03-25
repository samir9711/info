<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Vocabulary extends BaseModel
{
    protected $fillable = [
        'category_id' => 'category_id',
        'title' => 'title',
        'description' => 'description',
        'image' => 'image',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'title' => 'array',
        'description' => 'array',
    ];

    protected $search = ['title', 'description'];


    protected array $filterable = [
        'category_id'=>'int'
    ];

    protected array $dynamicFilterColumns = [
        'category_id' ,
    ];

    protected array $fileAttributes = [
        'image' => 'single',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //
}
