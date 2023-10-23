<?php

namespace App\Filament\Resources\JobProtocolResource\Pages;

use App\Filament\Resources\JobProtocolResource;
use Closure;
use Filament\Resources\Pages\ListRecords;

class ListJobProtocols extends ListRecords
{
    protected static string $resource = JobProtocolResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            JobProtocolResource\Widgets\QueueJobsCount::class,
            JobProtocolResource\Widgets\FailedJobsCount::class,
        ];
    }

    protected function getTableRecordActionUsing(): ?Closure
    {
        return fn (): string => 'result';
    }
}
