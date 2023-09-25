<?php

namespace App\Modules\ComputerMonitor\Commands;

use App\Modules\ComputerMonitor\Jobs\ComputersSyncJob;
use App\Modules\ComputerMonitor\Jobs\ComputerSyncJob;
use Illuminate\Console\Command;

class CheckComputerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pcmon:check-computer {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check computer status. Pass "all" as id to check all computers scope';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $id = $this->argument('id');

        if ($id === 'all') {
            ComputersSyncJob::dispatchSync();
        } else {
            ComputerSyncJob::dispatchSync($id);
        }
    }
}
