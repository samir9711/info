<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class CompanySkill extends BaseModel
{
    protected $fillable = [
        'company_id' => 'company_id',
        'skill_id' => 'skill_id',
        'description' => 'description',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'skill_id' => 'integer',
    ];

    protected array $filterable = [
        'company_id'=>'int',
        'skill_id'=>'int',
    ];

    protected array $dynamicFilterColumns = [
        'category_id' ,
        'skill_id'
    ];

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    //
}
