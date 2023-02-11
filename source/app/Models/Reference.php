<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    protected $fillable = [
        'name',
    ];

    public static function query(): Builder
    {
        return (new static(static::$referenceTable))->newQuery();
    }
}
