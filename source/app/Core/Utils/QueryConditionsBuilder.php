<?php

namespace App\Core\Utils;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class QueryConditionsBuilder
{
    public static function applyToQuery(Builder $query, array $conditions, array $context = []): void
    {
        static::parseGroups($query, $conditions, context: $context);
    }

    protected static function parseGroups(Builder $query, array $conditions, string $boolean = 'and', array $context = []): void
    {
        $query->where(function (Builder $group) use ($conditions, $context) {
            foreach ($conditions as $condition) {
                static::parseCondition($group, $condition, $context);
            }
        }, boolean: $boolean);
    }

    protected static function parseCondition(Builder $query, array $condition, array $context = []): void
    {
        $type = Arr::get($condition, 'type');

        if ($type === 'where') {
            $column = Arr::get($condition, 'data.field');
            $operator = Arr::get($condition, 'data.condition');
            $value = Arr::get($condition, 'data.value');

            $query->where($column, $operator, static::replacePlaceholders($value, $context));
        } elseif ($type === 'or' or $type === 'and') {
            $conditions = Arr::get($condition, "data.$type");
            static::parseGroups($query, $conditions, $type);
        } elseif ($type === 'raw') {
            $value = Arr::get($condition, 'data.value');

            $query->whereRaw(static::replacePlaceholders($value, $context));
        }
    }

    protected static function replacePlaceholders(string $value, array $context): string
    {
        $result = $value;

        foreach ($context as $placeholder => $replacement) {
            $result = Str::replace("{{{$placeholder}}}", $replacement, $result, false);
        }

        return $result;
    }
}
