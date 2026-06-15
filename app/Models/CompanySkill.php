<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CompanySkill extends BaseModel
{
    protected $fillable = [
        'company_id' => 'company_id',
        'name' => 'name',
        'description' => 'description',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'name' => 'array',
        'description' => 'array',
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    //
}
