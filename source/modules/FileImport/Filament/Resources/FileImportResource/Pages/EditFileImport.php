<?php

namespace App\Modules\FileImport\Filament\Resources\FileImportResource\Pages;

use App\Modules\FileImport\Filament\Resources\FileImportResource;
use App\Modules\FileImport\Jobs\FileImportJob;
use Filament\Facades\Filament;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFileImport extends EditRecord
{
    protected static string $resource = FileImportResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label(__('fileimport::messages.action.file import.title'))
                ->tooltip(__('fileimport::messages.action.file import.tooltip'))
                ->action('importFile'),
            Actions\DeleteAction::make(),
        ];
    }

    public function importFile(): void
    {
        FileImportJob::dispatch($this->getRecord()->getKey());
        Filament::notify('success', __('fileimport::messages.action.file import.success'));
    }
}
