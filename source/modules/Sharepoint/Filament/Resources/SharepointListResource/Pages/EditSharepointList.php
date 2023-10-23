<?php

namespace App\Modules\Sharepoint\Filament\Resources\SharepointListResource\Pages;

use App\Modules\Sharepoint\Filament\Resources\SharepointListResource;
use App\Modules\Sharepoint\Jobs\SyncSharepointListJob;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditSharepointList extends EditRecord
{
    protected static string $resource = SharepointListResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return trans_choice('sharepoint::messages.sharepoint list', 1);
    }

    protected function getActions(): array
    {
        return [
            Action::make('import')
                ->label(__('sharepoint::messages.action.sync list.title'))
                ->tooltip(__('sharepoint::messages.action.sync list.tooltip'))
                ->action('syncList'),

            DeleteAction::make(),
        ];
    }

    public function syncList(): void
    {
        SyncSharepointListJob::dispatch($this->getRecord()->getKey());
        Notification::make()->success()->title(__('sharepoint::messages.action.sync list.success'))->send();
    }
}
