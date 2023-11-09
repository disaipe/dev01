<?php

namespace App\Modules\OneC\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\OneC\Enums\DatabaseType;
use App\Modules\OneC\Models\OneCInfoBase;

class SyncOneCServersUsers extends ModuleScheduledJob
{
    public function work(): ?array
    {
        $servers = OneCInfoBase::query()
            ->distinct()
            ->whereNotNull('db_server')
            ->where('db_type', '=', DatabaseType::MSSQL)
            ->get('db_server')
            ->pluck('db_server');

        foreach ($servers as $server) {
            SyncOneCServerUsers::dispatch($server);
        }

        return [
            'count' => $servers->count(),
        ];
    }

    public function getDescription(): ?string
    {
        return __('onec::messages.job.server users sync.description');
    }
}
