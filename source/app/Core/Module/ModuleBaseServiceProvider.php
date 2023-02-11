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

    /**
     * Initialize module
     *
     * Method will be called after application boot only if module activated.
     *
     * @return void
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
     * @return void
     */
    protected function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Register and initialize module
     *
     * Not necessary change this in module.
     *
     * @return void
     */
    final public function boot(): void
    {
        /** @var Module $module */
        $module = app('modules')->register($this, $this->getKey());

        if ($module->isEnabled()) {
            $this->init();

            if ($this->options) {
                $module->setOptions($this->options);
            }
        }
    }

    protected function parseSchedule(string $periodName): array
    {
        $configName = Str::start($periodName, 'integration.');
        $configName = Str::finish($configName, '.syncPeriod');

        $periodData = config($configName) ?? [];

        $method = Arr::get($periodData, 'period') ?? 'disabled';
        $parameters = [];

        switch ($method) {
            case 'disabled':
                return [];
            case 'cron':
                $parameters = [$periodData['cron']];
                break;
            default:
                break;
        }

        return [$method, $parameters];
    }

    protected function scheduleJob(Schedule $schedule, ModuleScheduledJob $job, string $periodName = null): ?CallbackEvent
    {
        if (! $job->enabled()) {
            return null;
        }

        $scheduledJob = $schedule
            ->job($job)
            ->name(class_basename($job));

        if ($periodName) {
            @[$method, $parameters] = $this->parseSchedule($periodName);

            if ($method) {
                $scheduledJob->$method(...$parameters);
            }
        }

        return $scheduledJob;
    }

    /**
     * Returns module key
     *
     * @return string
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
