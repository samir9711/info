<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CompanyJob extends BaseModel
{
    protected $fillable = [
        'company_id' => 'company_id',
        'title' => 'title',
        'description' => 'description',
        'employment_type' => 'employment_type',
        'status' => 'status',
        'salary_min' => 'salary_min',
        'salary_max' => 'salary_max',
        'currency_id' => 'currency_id',
        'expires_at' => 'expires_at',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'salary_min' => 'integer',
        'salary_max' => 'integer',
        'currency_id' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    //
}
