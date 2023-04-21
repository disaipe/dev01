<?php

namespace App\Modules\DatabaseMonitor\Commands;

use App\Modules\DatabaseMonitor\Jobs\DatabaseServersSyncJob;
use Illuminate\Console\Command;

class CheckDatabaseServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbmon:check-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DatabaseServersSyncJob::dispatchSync();
    }
}
