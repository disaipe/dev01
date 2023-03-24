<?php

namespace App\Filament\Resources\CustomReferenceResource\Pages;

use App\Filament\Resources\CustomReferenceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomReferences extends ListRecords
{
    protected static string $resource = CustomReferenceResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
