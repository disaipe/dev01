<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;

class ServiceProvider extends ReferenceModel
{
    protected $fillable = [
        'name',
        'fullname',
        'identity',
        'description',
    ];
}
