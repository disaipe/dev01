<?php

namespace App\Filament\Resources\CustomReferenceResource\Pages;

use App\Filament\Resources\CustomReferenceResource;
use App\Services\CustomReferenceTableService;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomReference extends CreateRecord
{
    protected static string $resource = CustomReferenceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return CustomReferenceTableService::preserveSchemaFields($data);
    }
}
