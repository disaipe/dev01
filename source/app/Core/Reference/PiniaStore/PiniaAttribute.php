<?php

namespace App\Core\Reference\PiniaStore;

class PiniaAttribute
{
    public static function uid(): array
    {
        return ['uid'];
    }

    public static function attr($default = null): array
    {
        return ['attr', $default];
    }

    public static function string($default = ''): array
    {
        return ['string', $default];
    }

    public static function number($default = null): array
    {
        return ['number', $default];
    }

    public static function boolean($default = false): array
    {
        return ['boolean', $default];
    }

    public static function datetime($default = null): array
    {
        return ['datetime', $default];
    }

    public static function date($default = null): array
    {
        return ['date', $default];
    }

    public static function belongsTo($related, $foreignKey, $ownerKey = null): array
    {
        return ['belongsTo', $related, $foreignKey, $ownerKey];
    }

    public static function belongsToMany($related, $pivot, $foreignPivotKey, $relatedPivotKey): array
    {
        return ['belongsToMany', $related, $pivot, $foreignPivotKey, $relatedPivotKey];
    }

    public static function hasMany($related, $foreignKey, $localKey = null): array
    {
        return ['hasMany', $related, $foreignKey, $localKey];
    }

    public static function hasManyBy($related, $foreignKey, $localKey = null): array
    {
        return ['hasManyBy', $related, $foreignKey, $localKey];
    }
}
