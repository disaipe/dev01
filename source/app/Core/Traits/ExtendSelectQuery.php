<?php

namespace App\Core\Traits;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait ExtendSelectQuery {
    protected const SELECT_SCOPE = 'SELECT_SCOPE';

    protected static function extendSelect(Closure $makeQuery): void
    {
        // Some strange magic to append columns from ADUserEntry model.
        // This is required to be able to work with data as with a regular table.
        static::addGlobalScope(static::SELECT_SCOPE, function (Builder $builder) use ($makeQuery) {
            if (Str::startsWith($builder->withoutGlobalScope(static::SELECT_SCOPE)->toSql(), 'select')) {
                $originalQuery = $builder->getQuery();
                $bindings = $builder->getBindings();

                /** @var Builder $subQuery */
                $subQuery = $makeQuery($builder)->addSelect($builder->qualifyColumn('*'));

                $newQuery = DB::table(
                    DB::raw("({$subQuery->toSql()}) as `{$builder->getModel()->getTable()}`")
                );

                $builder
                    ->setQuery($newQuery)
                    ->mergeWheres($originalQuery->wheres, $bindings)
                    ->withoutGlobalScope(static::SELECT_SCOPE);

                if ($originalQuery->limit) {
                    $builder->limit($originalQuery->limit);
                }

                if ($originalQuery->offset) {
                    $builder->offset($originalQuery->offset);
                }
            }
        });
    }
}
