<?php

namespace App\Core\Enums;

enum QueryConditionOperator: string
{
    case EQUAL = '=';
    CASE EQUAL_OR_MORE = '>=';
    case EQUAL_OR_LESS = '<=';
    case NOT_EQUAL = '<>';
    case LIKE = 'like';
    case NOT_LIKE = 'not like';
    case IS_NULL = 'is null';
    case IS_NOT_NULL = 'is not null';

    public static function toValuesArray(): array
    {
        $results = [];

        foreach (self::cases() as $case) {
            $results[$case->value] = $case->value;
        }

        return $results;
    }
}
