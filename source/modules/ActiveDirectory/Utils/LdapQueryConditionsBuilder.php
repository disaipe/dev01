<?php

namespace App\Modules\ActiveDirectory\Utils;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LdapRecord\Query\Model\Builder;

class LdapQueryConditionsBuilder
{
    public static function applyToQuery(Builder $query, array $conditions): void
    {
        $query->rawFilter(static::parse($conditions));
    }

    public static function parse(array $conditions): string
    {
        return static::parseGroups($conditions);
    }

    protected static function parseGroups(array $conditions, string $boolean = 'and'): string
    {
        $sign = match ($boolean) {
            'and' => '&',
            'or' => '|',
            default => ''
        };
        $out = "($sign";

        foreach ($conditions as $condition) {
            $out .= static::parseCondition($condition);
        }

        $out .= ')';

        return $out;
    }

    protected static function parseCondition(array $condition): string
    {
        $type = Arr::get($condition, 'type');

        if ($type === 'where') {
            $value = Arr::get($condition, 'data.value');

            return Str::of($value)->start('(')->finish(')')->toString();
        } else if ($type === 'or' or $type === 'and') {
            $conditions = Arr::get($condition, "data.$type");

            return static::parseGroups($conditions, $type);
        }

        return '';
    }
}
