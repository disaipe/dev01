<?php

namespace App\Models;

use App\Core\ReferenceModel;

class Company extends ReferenceModel
{
    protected $fillable = [
        'code',
        'name',
        'fullname',
        'identity',
        'description',
    ];
}
