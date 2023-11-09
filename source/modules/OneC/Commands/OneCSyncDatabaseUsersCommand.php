<?php

namespace App\Modules\OneC\Commands;

use App\Modules\OneC\Jobs\SyncOneCServersUsers;
use App\Modules\OneC\Jobs\SyncOneCServerUsers;
use Illuminate\Console\Command;

class OneCSyncDatabaseUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onec:sync-users {db_server}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync 1C users from server databases';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $dbServer = $this->argument('db_server');
        if ($dbServer === 'all') {
            SyncOneCServersUsers::dispatch();
        } else {
            SyncOneCServerUsers::dispatch($dbServer);
        }
    }
}
