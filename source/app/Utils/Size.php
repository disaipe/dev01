<?php

namespace App\Utils;

class Size
{
    public static function Gigabyte(int $count = 1): int
    {
        return $count * 1000 ** 3;
    }

    public static function BToGB(int $bytes, int $precision = 2): float
    {
        return round($bytes / 1000 ** 3, $precision);
    }

    public static function KBToGB(float $kilobytes, int $precision = 2): float
    {
        return round($kilobytes / 1000 ** 2, $precision);
    }
}
