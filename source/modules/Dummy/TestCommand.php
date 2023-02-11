<?php

namespace App\Modules\Dummy;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dummy:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dummy test command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Dummy module enabled and command works');

        return Command::SUCCESS;
    }
}
