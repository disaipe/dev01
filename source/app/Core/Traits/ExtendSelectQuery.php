<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ExtendSelectQuery
{
    public function scopeExtended(Builder $builder): void
    {
        $builder->beforeQuery(function (\Illuminate\Database\Query\Builder $q) use ($builder) {
            $subQuery = $this
                ->extendSelect($builder)
                ->addSelect($builder->qualifyColumn('*'));

            $q->fromSub($subQuery, $builder->getModel()->getTable());
        });
    }

    protected function extendSelect(Builder $builder): Builder
    {
        return $builder;
    }
}
