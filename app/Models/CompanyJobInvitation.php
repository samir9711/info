<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CompanyJobInvitation extends BaseModel
{
    protected $fillable = [
        'company_id' => 'company_id',
        'company_job_id' => 'company_job_id',
        'user_id' => 'user_id',
        'title' => 'title',
        'message' => 'message',
        'status' => 'status',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'company_job_id' => 'integer',
        'user_id' => 'integer',
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function companyJob()
    {
        return $this->belongsTo(CompanyJob::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //
}
