<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Quiz extends BaseModel
{
    protected $fillable = [
        'title' => 'title',
        'description' => 'description',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    //
}
