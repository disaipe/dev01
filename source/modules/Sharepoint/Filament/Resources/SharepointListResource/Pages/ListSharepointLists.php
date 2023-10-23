<?php

namespace App\Modules\Sharepoint\Filament\Resources\SharepointListResource\Pages;

use App\Modules\Sharepoint\Filament\Resources\SharepointListResource;
use App\Modules\Sharepoint\Jobs\SyncSharepointListJob;
use App\Modules\Sharepoint\Models\SharepointList;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSharepointLists extends ListRecords
{
    protected static string $resource = SharepointListResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public static function syncList(SharepointList $record): void
    {
        SyncSharepointListJob::dispatch($record->getKey());
        Notification::make()->success()->title(__('sharepoint::messages.action.sync list.success'))->send();
    }
}
