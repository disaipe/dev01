<?php

namespace App\Utils;

class Domain
{
    public static function parseUserName(?string $username): array
    {
        if ($username) {
            return explode('\\', trim($username, '\\'));
        }

        return [null, $username];
    }
}
