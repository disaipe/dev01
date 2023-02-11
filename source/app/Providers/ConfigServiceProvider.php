<?php

namespace App\Providers;

use App\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        try {
            DB::getPdo();

            if (Schema::hasTable('system_configs')) {
                Config::load();
            }
        } catch (\Exception) {
        }
    }
}
