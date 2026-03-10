<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Translatable\HasTranslations;

class Role extends SpatieRole
{
    use HasTranslations;

    

    protected $fillable = [
        'name' => 'name',
        'guard_name' => 'guard_name',
        'display_name' => 'display_name',
    ];

protected $casts = [
    ];
}
