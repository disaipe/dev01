<?php

namespace App\Facades;

use App\Models\SystemConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Config
{
    /**
     * Loads all database-stored settings
     *
     * @return void
     */
    public static function load(): void
    {
        foreach (SystemConfig::all(['name', 'value']) as $config) {
            config([$config['name'] => $config['value']]);
        }
    }

    /**
     * Update config value and store it in database
     *
     * @param  string  $key config item name
     * @param  mixed|null  $value new item value
     * @param  bool  $encrypt encrypt value
     */
    public static function set(string $key, mixed $value = null, bool $encrypt = false): void
    {
        SystemConfig::query()->updateOrCreate(
            ['name' => $key],
            ['value' => $encrypt ? Crypt::encryptString($value) : $value]
        );
    }

    /**
     * Update multiple config values and store it in database
     *
     * @param  array  $values
     * @param  array  $types
     */
    public static function setArray(array $values, array $types = []): void
    {
        foreach ($values as $key => $_value) {
            $value = $_value;
            $type = Arr::get($types, $key);
            $encrypt = false;

            $isPassword = Str::contains($key, ['password'], true);

            if ($isPassword) {
                if (! $value) {
                    continue;
                }

                $type = 'password';
            }

            switch ($type) {
                case 'password':
                    $encrypt = true;
                    break;
                default:
                    break;
            }

            self::set($key, $value, $encrypt);
        }
    }

    public static function get(string $key, $default = null)
    {
        return config($key, $default);
    }
}
