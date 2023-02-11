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
}
