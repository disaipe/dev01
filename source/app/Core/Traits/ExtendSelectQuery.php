<?php

namespace App\Core\Traits;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait ExtendSelectQuery
{
    protected const SELECT_SCOPE = 'SELECT_SCOPE';

    protected static function extendSelect(Closure $makeQuery): void
    {
        // Some strange magic to append columns from ADUserEntry model.
        // This is required to be able to work with data as with a regular table.
        static::addGlobalScope(static::SELECT_SCOPE, function (Builder $builder) use ($makeQuery) {
            if (Str::startsWith($builder->withoutGlobalScope(static::SELECT_SCOPE)->toSql(), 'select')) {
                $builder->beforeQuery(function (\Illuminate\Database\Query\Builder $q) use ($makeQuery, $builder) {
                    $subQuery = $makeQuery($builder)
                        ->withoutGlobalScope(static::SELECT_SCOPE)
                        ->addSelect($builder->qualifyColumn('*'));

                    $q->fromSub($subQuery, $builder->getModel()->getTable());
                });
            }
        });
    }

    public function scopeWithoutSelectExtending(Builder $query): void
    {
        $query->withoutGlobalScope(static::SELECT_SCOPE);
    }
}
