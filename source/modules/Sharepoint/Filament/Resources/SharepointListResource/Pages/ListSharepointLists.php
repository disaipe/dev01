<?php

namespace App\Modules\Sharepoint\Filament\Resources\SharepointListResource\Pages;

use App\Modules\Sharepoint\Filament\Resources\SharepointListResource;
use App\Modules\Sharepoint\Jobs\SyncSharepointListJob;
use App\Modules\Sharepoint\Models\SharepointList;
use Filament\Facades\Filament;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSharepointLists extends ListRecords
{
    protected static string $resource = SharepointListResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function syncList(SharepointList $record): void
    {
        SyncSharepointListJob::dispatch($record->getKey());
        Filament::notify('success', __('sharepoint::messages.action.sync list.success'));
    }
}
