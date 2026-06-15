<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CompanyContactInfo extends BaseModel
{
    protected $fillable = [
        'company_id' => 'company_id',
        'phone' => 'phone',
        'whatsapp' => 'whatsapp',
        'contact_email' => 'contact_email',
        'website' => 'website',
        'facebook' => 'facebook',
        'x' => 'x',
        'linkedin' => 'linkedin',
        'instagram' => 'instagram',
        'youtube' => 'youtube',
        'contact_address' => 'contact_address',
        'working_hours' => 'working_hours',
    ];

    protected $casts = [
        'company_id' => 'integer',
    ];

    //
}
