<?php

namespace App\Modules\FileStorageMonitor\Commands;

use App\Modules\FileStorageMonitor\Jobs\FileStoragesSyncJob;
use App\Modules\FileStorageMonitor\Jobs\FileStorageSyncJob;
use Illuminate\Console\Command;

class FileStorageSizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file-storage:sync {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync file storage state';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $storageId = $this->argument('id');
        if ($storageId === 'all') {
            FileStoragesSyncJob::dispatchSync();
        } else {
            FileStorageSyncJob::dispatchSync($storageId);
        }
    }
}
