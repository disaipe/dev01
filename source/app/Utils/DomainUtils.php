<?php

namespace App\Utils;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DomainUtils
{
    public static function parseUserName(?string $username): array
    {
        if ($username) {
            return explode('\\', trim($username, '\\'));
        }

        return [null, $username];
    }

    public static function parseOUs(?string $ou): array
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
