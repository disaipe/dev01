<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Builder;

class Reference extends ReferenceModel
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static function query(): Builder
    {
        return (new static(static::$referenceTable))->newQuery();
    }
}
