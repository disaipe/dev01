<?php

namespace App\Core\Module;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Module base service provider
 *
 * Allows to initialize module and register views, translations, etc
 */
class ModuleBaseServiceProvider extends ServiceProvider
{
    /** @var ?string Module unique key */
    protected ?string $key = null;

    /** @var string Namespace to register views and translations */
    protected string $namespace = '*';

    /** @var ?array Module options */
    protected ?array $options = null;

    /** @var Module|null Module instance */
    protected ?Module $module = null;

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
        // insert sheduled jobs here
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
     * Register and initialize module
     *
     * Not necessary change this in module.
     */
    final public function boot(): void
    {
        /** @var Module $module */
        $module = app('modules')->register($this, $this->getKey());

        if ($module->isEnabled()) {
            $this->module = $module;

            $this->init();

            if ($this->options) {
                $module->setOptions($this->options);
            }
        }
    }

    protected function scheduleJob(Schedule $schedule, ModuleScheduledJob $job, string $jobName = null): ?CallbackEvent
    {
        $enabled = (bool)$this->module->getConfig("$jobName.enabled");

        if (!$enabled) {
            return null;
        }

        $cron = $this->module->getConfig("$jobName.schedule");

        if ($cron) {
            $scheduledJob = $schedule
                ->job($job, 'default')
                ->name(class_basename($job))
                ->description($job->description)
                ->cron($cron);

            return $scheduledJob;
        }

        return null;
    }

    /**
     * Returns module key
     */
    private function getKey(): string
    {
        if ($this->key) {
            return $this->key;
        }

        $p = explode('\\', static::class);

        return Str::camel($p[count($p) - 2]);
    }
}
