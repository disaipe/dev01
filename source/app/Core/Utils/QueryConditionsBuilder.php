<?php

namespace App\Core\Utils;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class QueryConditionsBuilder
{
    public static function applyToQuery(Builder $query, array $conditions): void
    {
        static::parseGroups($query, $conditions);
    }

    protected static function parseGroups(Builder $query, array $conditions, string $boolean = 'and'): void
    {
        $query->where(function (Builder $group) use ($conditions) {
            foreach ($conditions as $condition) {
                static::parseCondition($group, $condition);
            }
        }, boolean: $boolean);
    }

    protected static function parseCondition(Builder $query, array $condition): void
    {
        $type = Arr::get($condition, 'type');

        if ($type === 'where') {
            $column = Arr::get($condition, 'data.field');
            $operator = Arr::get($condition, 'data.condition');
            $value = Arr::get($condition, 'data.value');

            $query->where($column, $operator, $value);
        } elseif ($type === 'or' or $type === 'and') {
            $conditions = Arr::get($condition, "data.$type");
            static::parseGroups($query, $conditions, $type);
        }
    }
}
