<?php

namespace App\Core\Module;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ModuleManager
{
    private array $modules = [];

    /**
     * Register module with given options
     *
     * @param  string  $key module unique key
     * @param  array  $options module optionsi
     */
    public function register(ModuleBaseServiceProvider $provider, string $key, array $options = []): Module
    {
        $module = new Module($provider, $key, $options);
        Arr::set($this->modules, $key, $module);

        return $module;
    }

    /**
     * Returns all registered modules
     */
    public function getModules(bool $onlyEnabled = false): array
    {
        if (! $onlyEnabled) {
            return $this->modules;
        }

        return Arr::where($this->modules, fn (Module $module) => $module->isEnabled());
    }

    /**
     * Get module by given key
     *
     * @param  string  $key module unique key
     * @return ?Module
     */
    public function getByKey(string $key): ?Module
    {
        $_key = Str::camel($key);

        return Arr::first($this->modules, fn (Module $module) => $module->getKey() === $_key);
    }

    public function getByNamespace(string $namespace): ?Module
    {
        preg_match("/App\\\Modules\\\(.*?)\\\.*/", $namespace, $matches);

        if ($matches && count($matches)) {
            return $this->getByKey($matches[1]);
        }

        return null;
    }

    /**
     * Get module by given slug
     *
     * @param  string  $slug module slug
     * @return ?Module
     */
    public function getBySlug(string $slug): ?Module
    {
        return Arr::first($this->modules, fn (Module $module) => $module->getSlug() === $slug);
    }
}
