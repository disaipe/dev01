<?php

namespace App\Filament\Resources\JobProtocolResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class QueueJobsCount extends Widget
{
    protected static string $view = 'filament.resources.job-protocol-resource.widgets.queue-jobs-count';

    protected string $title = 'Количество заданий в очереди';

    public function getQueuedCount(): int
    {
        return DB::table('jobs')->count();
    }

    protected function getViewData(): array
    {
        return [
            'title' => $this->title,
            'queuedCount' => $this->getQueuedCount(),
        ];
    }
}
