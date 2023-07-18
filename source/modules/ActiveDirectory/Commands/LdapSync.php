<?php

namespace App\Modules\ActiveDirectory\Commands;

use App\Modules\ActiveDirectory\Job\ADSyncJob;
use Illuminate\Console\Command;

class LdapSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ad:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ADSyncJob::dispatchSync();

        return Command::SUCCESS;
    }
}
