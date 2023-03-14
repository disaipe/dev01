<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Models\ServiceProvider;

class ServiceProviderReference extends ReferenceEntry
{
    protected string $model = ServiceProvider::class;

    protected bool $piniaBindings = false;

    protected int $order = 100;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->required()
                ->visible(),

            'fullname' => ReferenceFieldSchema::make()
                ->label('Полное наименование'),

            'identity' => ReferenceFieldSchema::make()
                ->label('ИНН')
                ->max(32)
                ->visible(),

            'description' => ReferenceFieldSchema::make()
                ->label('Описание'),
        ];
    }
}
