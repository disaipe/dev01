<?php

namespace App\Providers;

use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Module\ModuleManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

/**
 * Modules service provider
 *
 * Detects and register installed modules
 */
class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('modules', fn () => new ModuleManager());

        // check prevents module registration attemt before table was created
        try {
            DB::getPdo();

            if (Schema::hasTable('modules')) {
                $this->registerModules();
            }
        } catch (\Exception) {
        }
    }

    /**
     * Detect and register modules from directory
     *
     * @return void
     */
    private function registerModules(): void
    {
        $finder = new Finder();
        $files = $finder
            ->in(config('module.base'))
            ->files()
            ->name(config('module.patterns'));

        foreach ($files as $file) {
            include $file->getPath().'/'.$file->getFilename();
        }

        $classes = get_declared_classes();

        $modulesProviders = Arr::where($classes, function ($class) {
            return Str::startsWith($class, 'App\\Modules\\')
                && get_parent_class($class) === ModuleBaseServiceProvider::class;
        });

        foreach ($modulesProviders as $provider) {
            $this->app->register($provider);
        }
    }
}
