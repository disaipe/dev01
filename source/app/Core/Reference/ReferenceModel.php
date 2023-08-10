<?php

namespace App\Core\Reference;

use App\Core\Traits\Protocolable;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferenceModel extends Model
{
    use SoftDeletes, Protocolable;

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope());
    }
}
