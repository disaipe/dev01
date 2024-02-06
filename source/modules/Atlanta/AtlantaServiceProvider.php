<?php

namespace App\Modules\Atlanta;

use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Reference\ReferenceManager;
use App\Modules\Atlanta\References\AtlantaUserReference;
use App\Services\DashboardMenuService;
use App\Support\DashboardMenuItem;

class AtlantaServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'atlanta';

    public function init(): void
    {
        $this->loadMigrations();

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(AtlantaUserReference::class);

        /** @var DashboardMenuService $menu */
        $menu = app('menu');
        $menu->addMenuItem(DashboardMenuItem::make('atlanta')->label('Атланта')->icon('tabler:sum'));
    }

    public function getOptions(): array
    {
        return [
            'name' => __('atlanta::messages.name'),
            'description' => __('atlanta::messages.description'),
        ];
    }
}
