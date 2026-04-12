<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class Certificate extends BaseModel
{
    protected $fillable = [
        'user_id' => 'user_id',
        'course_id' => 'course_id',
        'meta' => 'meta',
        'certificate_number' => 'certificate_number',
        'score_percent' => 'score_percent',
        'passing_mark' => 'passing_mark',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_id' => 'integer',
        'meta' => 'array',
        'score_percent' => 'integer',
        'passing_mark' => 'integer',
    ];


    protected array $filterable = [
        'user_id'=>'int',
        'course_id'=>'int',
    ];

    protected array $dynamicFilterColumns = [
        'user_id' ,
        'course_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    //
}
