<?php

namespace App\Core\Module;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * Module base service provider
 *
 * Allows to initialize module and register views, translations, etc
 */
class ModuleBaseServiceProvider extends ServiceProvider
{
    /** @var ?string Module directory */
    protected ?string $basePath = __DIR__;

    /** @var ?string Module unique key */
    protected ?string $key = null;

    /** @var string Namespace to register views and translations */
    protected string $namespace = '*';

    /** @var ?array Module options */
    protected ?array $options = null;

    /** @var Module|null Module instance */
    protected ?Module $module = null;

    /**
     * Make actions on module booting
     *
     * @return void
     */
    public function onBooting()
    {
        // insert code here
    }

    /**
     * Initialize module
     *
     * Method will be called after application boot only if module activated.
     */
    public function init(): void
    {
        // insert code here
    }

    public function schedule(Schedule $schedule)
    {
        // insert scheduled jobs here
    }

    /**
     * Set module options
     *
     * @param  array  $options module options to set
     */
    protected function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Register service provider
     */
    final public function register(): void
    {
        $this->app->booting(function () {
            $this->onBooting();
        });
    }

    /**
     * Register and initialize module
     *
     * Not necessary change this in module.
     */
    final public function boot(): void
    {
        $classInfo = new ReflectionClass($this);
        $this->basePath = Arr::get(pathinfo($classInfo->getFileName()), 'dirname', '');

        /** @var Module $module */
        $module = app('modules')->register($this, $this->getKey());
        $this->module = $module;

        if ($module && $module->isEnabled()) {
            $this->init();

            if ($this->options) {
                $module->setOptions($this->options);
            }
        }
    }

    public function getMigrationsDirectory($absolute = false): string
    {
        if ($absolute) {
            return $this->basePath.'/migrations';
        }

        return Str::replace(base_path().'/', '', $this->basePath).'/migrations';
    }

    public function getTranslationsDirectory(): string
    {
        return $this->basePath.'/resources/lang';
    }

    public function getViewsDirectory(): string
    {
        return $this->basePath.'/resources/view';
    }

    public function loadTranslations(): void
    {
        $this->loadTranslationsFrom($this->getTranslationsDirectory(), $this->namespace);
    }

    public function loadMigrations(): void
    {
        $this->loadMigrationsFrom($this->getMigrationsDirectory());
    }

    public function loadViews(): void
    {
        $this->loadViewsFrom($this->getViewsDirectory(), $this->namespace);
    }

    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    protected function scheduleJob(Schedule $schedule, ModuleScheduledJob $job, string $jobName = null): ?CallbackEvent
    {
        $enabled = (bool) $this->module->getConfig("$jobName.enabled");

        if (! $enabled) {
            return null;
        }

        $cron = $this->module->getConfig("$jobName.schedule");

        return $cron
            ? $this->scheduleCronJob($schedule, $job, $cron)
            : null;
    }

    protected function scheduleCronJob(Schedule $schedule, ModuleScheduledJob $job, string $cron): ?CallbackEvent
    {
        return $schedule
            ->job($job, 'default')
            ->name(class_basename($job))
            ->description($job->description)
            ->cron($cron);
    }

    /**
     * Returns module key
     */
    protected function getKey(): string
    {
        if ($this->key) {
            return $this->key;
        }

        $p = explode('\\', static::class);

        return Str::camel($p[count($p) - 2]);
    }
}
