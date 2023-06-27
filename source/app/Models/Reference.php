<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;

class Reference extends ReferenceModel
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
