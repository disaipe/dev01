<?php

namespace App\Modules\ManageEngineSD;

use App\Core\Module\ModuleManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class SDConnection
{
    const NAME = 'manengine';

    public static function Setup(array $config = []): void
    {
        $savedConfig = self::Config();

        if (is_array($config)) {
            $config = array_merge($savedConfig, $config);
        }

        $connection = [
            'driver' => 'pgsql',
            'host' => Arr::get($config, 'db_host'),
            'port' => Arr::get($config, 'db_port') ?? 5432,
            'database' => Arr::get($config, 'db_name') ?? 'postgres',
            'username' => Arr::get($config, 'db_username'),
            'password' => Arr::get($config, 'db_password'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => Arr::get($config, 'ssl') ?? 'prefer',
        ];

        Config::set('database.connections.'.static::NAME, $connection);
    }

    public static function Config(): array
    {
        /** @var ModuleManager $modules */
        $modules = app('modules');
        $module = $modules->getByKey('manageEngineSD');

        if (!$module) {
            return [];
        }

        return $module->getConfig();
    }
}
