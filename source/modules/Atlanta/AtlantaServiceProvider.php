<?php

namespace App\Modules\Atlanta;

use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Reference\ReferenceManager;
use App\Modules\Atlanta\References\AtlantaUserReference;

class AtlantaServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'atlanta';

    public function init(): void
    {
        $this->loadMigrations();

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(AtlantaUserReference::class);
    }

    public function getOptions(): array
    {
        return [
            'name' => __('atlanta::messages.name'),
            'description' => __('atlanta::messages.description'),
        ];
    }
}
