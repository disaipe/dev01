<?php

namespace App\Modules\DatabaseMonitor\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\DatabaseMonitor\Models\DatabaseServer;

class DatabaseServersSyncJob extends ModuleScheduledJob
{
    public function work(): ?array
    {
        $databases = DatabaseServer::query()
            ->enabled()
            ->get();

        $databases->each(fn (DatabaseServer $server) => DatabaseServerSyncJob::dispatch($server->getKey()));

        return [
            'Result' => 'Started: ' . $databases->count()
        ];
    }

    public function getDescription(): ?string
    {
        return __('dbmon::messages.job.databases sync.title');
    }
}
