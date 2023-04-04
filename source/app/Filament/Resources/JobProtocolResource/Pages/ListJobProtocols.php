<?php

namespace App\Filament\Resources\JobProtocolResource\Pages;

use App\Filament\Resources\JobProtocolResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobProtocols extends ListRecords
{
    protected static string $resource = JobProtocolResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JobProtocolResource\Widgets\QueueJobsCount::class,
            JobProtocolResource\Widgets\FailedJobsCount::class,
        ];
    }
}
