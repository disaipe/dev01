<?php

namespace App\Modules\OneC\Commands;

use App\Modules\OneC\Jobs\SyncOneCListsJob;
use Illuminate\Console\Command;

class OneCSyncListsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onec:sync-lists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync 1C information bases lists';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        SyncOneCListsJob::dispatch();
    }
}
