<?php

namespace App\Models;

use App\Core\ReferenceModel;

class ServiceProvider extends ReferenceModel
{
    protected $fillable = [
        'name',
        'fullname',
        'identity',
        'description'
    ];
}
