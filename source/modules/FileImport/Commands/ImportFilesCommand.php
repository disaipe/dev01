<?php

namespace App\Modules\FileImport\Commands;

use App\Modules\FileImport\Jobs\FilesImportJob;
use Illuminate\Console\Command;

class ImportFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fileimport:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from files';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        FilesImportJob::dispatchSync();
    }
}
