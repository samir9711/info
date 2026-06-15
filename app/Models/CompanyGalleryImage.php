<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CompanyGalleryImage extends BaseModel
{
    protected $fillable = [
        'company_id' => 'company_id',
        'image_path' => 'image_path',
        'caption' => 'caption',
        'sort_order' => 'sort_order',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    //
}
