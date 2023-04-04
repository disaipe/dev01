<?php

namespace App\Console;

use App\Core\Module\Module;
use App\Core\Module\ModuleManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected Schedule $schedule;

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $this->schedule = $schedule;

        // Schedule jobs from modules
        $this->scheduleModules();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Schedule jobs from active modules
     *
     * @return void
     */
    private function scheduleModules(): void {
        /** @var ModuleManager $modules */
        $modulesManager = app('modules');

        if ($modulesManager) {
            /** @var Module[] $modules */
            $modules = $modulesManager->getModules(true);

            foreach ($modules as $module) {
                $module->getProvider()->schedule($this->schedule);
            }
        }
    }
}
