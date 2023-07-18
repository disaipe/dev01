<?php

namespace App\Modules\MSExchangeMonitor\Commands;

use App\Modules\MSExchangeMonitor\Jobs\MSExchangeStatsSyncJob;
use Illuminate\Console\Command;

class MSExchangeStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ms-exchange:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync MS Exchange mailboxes stats';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        MSExchangeStatsSyncJob::dispatchSync();
    }
}
