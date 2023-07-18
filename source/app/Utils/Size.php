<?php

namespace App\Utils;

class Size
{
    public static function Gigabyte(int $count = 1): int
    {
        return $count * 1000 * 1000 * 1000;
    }

    public static function ToGigabytes(int $bytes, int $precision = 2): float
    {
        return round($bytes / 1000 / 1000 / 1000, $precision);
    }

    public static function ToGibibytes(int $bytes, int $precision = 2): float
    {
        return round($bytes / 1024 / 1024 / 1024, $precision);
    }
}
