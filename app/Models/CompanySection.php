<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CompanySection extends BaseModel
{
    protected $fillable = [
        'company_id' => 'company_id',
        'title' => 'title',
        'description' => 'description',
        'image_path' => 'image_path',
        'sort_order' => 'sort_order',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'title' => 'array',
        'description' => 'array',
        'sort_order' => 'integer',
    ];

    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
