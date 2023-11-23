<?php

namespace App\Modules\Sharepoint\Filament\Resources\SharepointListResource\Pages;

use App\Modules\Sharepoint\Filament\Resources\SharepointListResource;
use Filament\Actions\CreateAction;
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
}
