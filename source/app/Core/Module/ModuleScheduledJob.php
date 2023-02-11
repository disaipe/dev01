<?php

namespace App\Core\Module;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class ModuleScheduledJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    protected Module $module;

    abstract public function handle();

    public function enabled(): bool
    {
        return true;
    }

    protected function getModule(): ?Module
    {
        $modules = app('modules');

        return $modules->getByNamespace(get_class($this));
    }

    protected function getModuleConfig(): array
    {
        $module = $this->getModule();

        if ($module) {
            return $module->getConfig();
        }

        return [];
    }
}
