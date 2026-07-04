<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CompanyJobApplication extends BaseModel
{
    protected $fillable = [
        'company_job_id' => 'company_job_id',
        'company_id' => 'company_id',
        'user_id' => 'user_id',
        'cover_letter' => 'cover_letter',
        'cv' => 'cv',
        'status' => 'status',
        'reviewed_at' => 'reviewed_at',
        'company_note' => 'company_note',
    ];

    protected $casts = [
        'company_job_id' => 'integer',
        'company_id' => 'integer',
        'user_id' => 'integer',
        'reviewed_at' => 'datetime',
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
