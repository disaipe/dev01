<?php

namespace App\Modules\FileImport\Filament\Resources\FileImportResource\Pages;

use App\Modules\FileImport\Filament\Resources\FileImportResource;
use App\Modules\FileImport\Jobs\FileImportJob;
use App\Modules\FileImport\Models\FileImport;
use Filament\Facades\Filament;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFileImports extends ListRecords
{
    protected static string $resource = FileImportResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function importFile(FileImport $record): void
    {
        FileImportJob::dispatch($record->getKey());
        Filament::notify('success', __('fileimport::messages.action.file import.success'));
    }
}
