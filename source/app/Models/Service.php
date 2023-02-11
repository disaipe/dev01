<?php

namespace App\Models;

use App\Core\ReferenceModel;

class Service extends ReferenceModel
{
    protected $fillable = [
        'parent_id',
        'name',
        'display_name',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];
}
