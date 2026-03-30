<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class InstructorRating extends BaseModel
{
    protected $fillable = [
        'instructor_id' => 'instructor_id',
        'user_id' => 'user_id',
        'rating' => 'rating',
        'comment' => 'comment',
    ];

    protected $casts = [
        'instructor_id' => 'integer',
        'user_id' => 'integer',
        'rating' => 'integer',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //
}
