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
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'name' => 'array',
        'description' => 'array',
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

    //
}
