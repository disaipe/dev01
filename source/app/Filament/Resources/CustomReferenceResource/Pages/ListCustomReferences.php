<?php

namespace App\Filament\Resources\CustomReferenceResource\Pages;

use App\Filament\Resources\CustomReferenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomReferences extends ListRecords
{
    protected static string $resource = CustomReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
