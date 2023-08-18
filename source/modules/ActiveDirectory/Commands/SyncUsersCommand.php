<?php

namespace App\Modules\ActiveDirectory\Commands;

use App\Modules\ActiveDirectory\Job\ADSyncUsersJob;
use Illuminate\Console\Command;

class SyncUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ad:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start user records syncing job from Active Directory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ADSyncUsersJob::dispatchSync();

        return Command::SUCCESS;
    }
}
