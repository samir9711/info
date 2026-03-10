<?php

namespace App\Models;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;

class ArticleSection extends BaseModel
{
    protected $fillable = [
        'article_id' => 'article_id',
        'title' => 'title',
        'body' => 'body',
        'conclusion' => 'conclusion',
        'image' => 'image',
    ];

    protected $casts = [
        'article_id' => 'integer',
        'title' => 'array',
        'body' => 'array',
        'conclusion' => 'array',
    ];

    protected array $fileAttributes = [
        'image' => 'single',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    //
}
