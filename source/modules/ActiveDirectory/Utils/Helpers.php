<?php

namespace App\Modules\ActiveDirectory\Utils;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Helpers
{
    /**
     * Parse multiline OU list to array
     */
    public static function ParseOUs(?string $ou): array
    {
        if (! $ou) {
            return [];
        }

        $ous = explode("\n", $ou);

        return Arr::where($ous, function (string $ou) {
            $trimmed = trim($ou);

            if (! strlen($trimmed)) {
                return false;
            }

            if (Str::startsWith($ou, ['//', '#'])) {
                return false;
            }

            return true;
        });
    }
}
