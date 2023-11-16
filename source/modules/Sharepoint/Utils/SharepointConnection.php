<?php

namespace App\Modules\Sharepoint\Utils;

use App\Core\Module\ModuleManager;
use App\Support\SqlServerConnection;

class SharepointConnection extends SqlServerConnection
{
    const NAME = 'sharepoint';

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
}
