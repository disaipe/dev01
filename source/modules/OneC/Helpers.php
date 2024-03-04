<?php

namespace App\Modules\OneC;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Helpers
{
    public static function decryptData(string $data): string|false {
        $j = 1;
        $result = '';

        for ($i = ord($data[0]) + 1; $i <= strlen($data) - 1; $i++) {
            if ($j > ord($data[0])) {
                $j = 1;
            }

            $result .= chr(ord($data[$i]) ^ ord($data[$j]));
            $j++;
        }

        $clean = preg_replace('/[^[:print:]]/', '', $result);

        return Str::startsWith($clean,'{') ? $clean : false;
    }

    public static function extractUserData(string $data): ?array
    {
        $re = '/{(?<uid>[a-fA-F\d-]{36}),"(?<login>.*?)","(?<unknown_1>.*?)","(?<name>.*?)",(?<uid_1>[a-fA-F\d-]{36}),.*?{(?<role_count>\d+),?(?<role_uid_list>.*?)},(?<uid_2>[a-fA-F\d-]{36}),(?<unknown_2>\d),(?<unknown_3>\d),(?<unknown_4>[a-fA-F\d-]*?),(?<allow_login>\d),(?<unknown_5>\d),"(?<hash_1>.*?)","(?<hash_2>.*?)"/ms';
        preg_match($re, $data, $matches);

        if (! is_array($matches)) {
            return null;
        }

        // Forget not interesting at now properties
        Arr::forget($matches, ['role_uid_list']);

        if (! count($matches)) {
            return null;
        }

        return Arr::where($matches, fn ($value, $key) => !is_int($key));
    }

    public static function decryptUserData(string $data): ?array
    {
        $decrypted = static::decryptData($data);

        if (! $decrypted) {
            return null;
        }

        return static::extractUserData($decrypted);
    }
}
