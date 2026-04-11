<?php

namespace App\Models;

use App\Models\BaseModel;

use App\Support\HasTags;
use Illuminate\Database\Eloquent\Model;

class Course extends BaseModel
{
    use HasTags;
    protected $fillable = [
        'category_id' => 'category_id',
        'title' => 'title',
        'subtitle' => 'subtitle',
        'short_description' => 'short_description',
        'description' => 'description',
        'image' => 'image',
        'is_free' => 'is_free',
        'price' => 'price',
        'currency_id' => 'currency_id',
        'publish' => 'publish',
        'published_at' => 'published_at',
        'is_featured' => 'is_featured',
        'level' => 'level',
        'expected_hours' => 'expected_hours',
        'what_will_learn' => 'what_will_learn',
        'created_by' => 'created_by',
        'approval_status' => 'approval_status',
        'is_platform_owned' => 'is_platform_owned',
        'rejection_reason' => 'rejection_reason',
        'profit_percentage'=>'profit_percentage',
        'video_intro' => 'video_intro'

    ];

    protected $casts = [
        'category_id' => 'integer',
        'title' => 'array',
        'subtitle' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'is_free' => 'boolean',
        'price' => 'float',
        'currency_id' => 'integer',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'level' => 'string',
        'expected_hours' => 'integer',
        'what_will_learn' => 'array',
        'created_by' => 'integer',
        'approval_status' => 'integer',
        'is_platform_owned' => 'boolean',
        'rejection_reason' => 'string',
        'profit_percentage'=>'float',
        'video_intro' => 'string'
    ];


    protected $search = ['title', 'description'];


    protected array $filterable = [
        'category_id' => 'int',
        'category_tree_id' => [
            'column' => 'category_id',
            'op' => 'tree',
            'type' => 'int',
        ],
        'is_free' => 'bool',
        'is_featured' => 'bool',
        'approval_status' => 'int',
        'is_platform_owned'=>'bool'

    ];

    protected array $treeFilterModels = [
        'category_id' => Category::class,
    ];

    protected array $dynamicFilterColumns = [
        'category_id' ,
        'is_free',
        'is_featured',
        'approval_status',
        'is_platform_owned'
    ];

    protected array $fileAttributes = [
        'video_intro' => 'single',
        'image' => 'single',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function instructors()
    {
        return $this->belongsToMany(Instructor::class,'course_instructors','course_id','instructor_id')
        ->withTimestamps();
    }

    public function applications()
    {
        return $this->hasMany(CourseApplication::class);
    }

    public function approvedApplications()
    {
        return $this->hasMany(CourseApplication::class)->where('status', 1);
    }

    //
}
