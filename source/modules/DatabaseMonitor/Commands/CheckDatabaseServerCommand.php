<?php

namespace App\Modules\DatabaseMonitor\Commands;

use App\Modules\DatabaseMonitor\Jobs\DatabaseServerSyncJob;
use Illuminate\Console\Command;

class CheckDatabaseServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbmon:check-server {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get database stats from server';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $serverId = $this->argument('id');
        DatabaseServerSyncJob::dispatchSync($serverId);
    }
}
