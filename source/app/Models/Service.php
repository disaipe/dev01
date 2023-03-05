<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;

class Service extends ReferenceModel
{
    protected $fillable = [
        'parent_id',
        'name',
        'display_name',
        'tags',
        'indicator_code',
    ];

    protected $casts = [
        'tags' => 'array',
    ];
}
