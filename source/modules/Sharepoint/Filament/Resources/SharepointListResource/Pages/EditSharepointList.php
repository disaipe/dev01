<?php

namespace App\Modules\Sharepoint\Filament\Resources\SharepointListResource\Pages;

use App\Modules\Sharepoint\Filament\Resources\SharepointListResource;
use App\Modules\Sharepoint\Jobs\SyncSharepointListJob;
use Filament\Facades\Filament;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditSharepointList extends EditRecord
{
    protected static string $resource = SharepointListResource::class;

    protected function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getSubheading(): string|Htmlable|null
    {
        return trans_choice('sharepoint::messages.sharepoint list', 1);
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label(__('sharepoint::messages.action.sync list.title'))
                ->tooltip(__('sharepoint::messages.action.sync list.tooltip'))
                ->action('syncList'),

            Actions\DeleteAction::make(),
        ];
    }

    public function syncList(): void
    {
        SyncSharepointListJob::dispatch($this->getRecord()->getKey());
        Filament::notify('success', __('sharepoint::messages.action.sync list.success'));
    }
}
