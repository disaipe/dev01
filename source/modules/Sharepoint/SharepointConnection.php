<?php

namespace App\Modules\Sharepoint;

use App\Core\Module\ModuleManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class SharepointConnection
{
    const NAME = 'sharepoint';

    public static function Setup(array $config = []): void
    {
        $savedConfig = self::Config();

        if (is_array($config)) {
            $config = array_merge($savedConfig, $config);
        }

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
            // TODO: find the way  to connection with port
            Arr::set($connection, 'port', null);
        }

        Config::set('database.connections.'.static::NAME, $connection);
    }

    public static function Config(): array
    {
        /** @var ModuleManager $modules */
        $modules = app('modules');
        $module = $modules->getByKey('sharepoint');

        if (! $module) {
            return [];
        }

        return $module->getConfig();
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
