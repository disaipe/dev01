<?php

namespace App\Modules\Directum\Commands;

use App\Modules\Directum\Jobs\DirectumSyncUsersJob;
use Illuminate\Console\Command;

class DirectumSyncUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'directum:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Directum users';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DirectumSyncUsersJob::dispatchSync();
    }
}
