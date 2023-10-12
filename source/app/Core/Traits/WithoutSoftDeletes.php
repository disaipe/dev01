<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\SoftDeletingScope;

trait WithoutSoftDeletes
{
    public static function bootWithoutSoftDeletes(): void
    {
        $scopes = static::getAllGlobalScopes();
        unset($scopes[static::class][SoftDeletingScope::class]);

        static::setAllGlobalScopes($scopes);
    }
}
