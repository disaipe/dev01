<?php

namespace App\Modules\OneC\Commands;

use App\Modules\OneC\Jobs\SyncOneCServerListJob;
use App\Modules\OneC\Jobs\SyncOneCServersListsJob;
use Illuminate\Console\Command;

class OneCSyncServerListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onec:sync-server-list {server=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync 1C server list';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $server = $this->argument('server');

        if ($server === 'all') {
            SyncOneCServersListsJob::dispatch();
        } else {
            SyncOneCServerListJob::dispatch($server);
        }
    }
}
