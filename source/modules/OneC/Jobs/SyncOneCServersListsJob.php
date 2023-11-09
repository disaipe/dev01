<?php

namespace App\Modules\OneC\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\OneC\Models\OneCInfoBase;

class SyncOneCServersListsJob extends ModuleScheduledJob
{
    public function work(): ?array
    {
        $servers = OneCInfoBase::query()
            ->whereNotNull('server')
            ->distinct()
            ->get('server')
            ->pluck('server');

        foreach ($servers as $server) {
            SyncOneCServerListJob::dispatch($server);
        }

        return [
            'servers' => $servers->count(),
        ];
    }

    public function getDescription(): ?string
    {
        return __('onec::messages.job.server list sync.description');
    }
}
