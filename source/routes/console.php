<?php

use App\Core\Module\Module;
use App\Core\Module\ModuleManager;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Schedule jobs from active modules
 */

/** @var ModuleManager $modules */
$modulesManager = app('modules');

if ($modulesManager) {
    /** @var Module[] $modules */
    $modules = $modulesManager->getModules(true);

    foreach ($modules as $module) {
        $module->getProvider()->schedule(Schedule::getFacadeRoot());
    }
}