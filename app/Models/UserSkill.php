<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class UserSkill extends BaseModel
{
    protected $fillable = [
        'user_id' => 'user_id',
        'skill_id' => 'skill_id',
        'note' => 'note',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'skill_id' => 'integer',
    ];

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //
}
