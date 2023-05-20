<?php

namespace App\Modules\DatabaseMonitor\Commands;

use App\Modules\DatabaseMonitor\Jobs\DatabaseServersSyncJob;
use Illuminate\Console\Command;

class CheckDatabaseServersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbmon:check-servers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get database stats from all servers';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DatabaseServersSyncJob::dispatchSync();
    }
}
