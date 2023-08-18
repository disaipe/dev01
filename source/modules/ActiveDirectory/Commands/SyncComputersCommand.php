<?php

namespace App\Modules\ActiveDirectory\Commands;

use App\Modules\ActiveDirectory\Job\ADSyncComputersJob;
use Illuminate\Console\Command;

class SyncComputersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ad:sync-computers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start computer records syncing job from Active Directory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ADSyncComputersJob::dispatchSync();

        return Command::SUCCESS;
    }
}
