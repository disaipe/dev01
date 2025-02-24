<?php

namespace App\Modules\FileImport\Filament\Resources\FileImportResource\Pages;

use App\Modules\FileImport\Filament\Resources\FileImportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFileImports extends ListRecords
{
    protected static string $resource = FileImportResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
