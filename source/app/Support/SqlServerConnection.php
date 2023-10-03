<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class SqlServerConnection
{
    public static function Setup(string $name, array $config = []): void
    {
        $connection = [
            'driver' => Arr::get($config, 'db_driver'),
            'host' => Arr::get($config, 'db_host'),
            'port' => Arr::get($config, 'db_port'),
            'database' => Arr::get($config, 'db_name'),
            'username' => Arr::get($config, 'db_username'),
            'password' => Arr::get($config, 'db_password'),
        ];

        $driverOptions = self::getDriverOptions(Arr::get($config, 'driver_options'));
        $connection = array_merge($connection, $driverOptions);

        if (Arr::get($connection, 'driver') === 'sqlsrv') {
            // TODO: find the way to make connection with port
            Arr::set($connection, 'port', null);
        }

        Config::set("database.connections.{$name}", $connection);
    }

    protected static function getDriverOptions(?string $options): array
    {
        if (! $options) {
            return [];
        }

        $matches = null;
        preg_match_all('/(?<key>.+?)=(?<value>.+?)/', $options, $matches, PREG_SET_ORDER);

        if ($matches) {
            return collect($matches)
                ->mapWithKeys(fn ($match) => [Arr::get($match, 'key') => Arr::get($match, 'value')])
                ->toArray();
        }

        return [];
    }
}
