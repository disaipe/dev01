<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Models\Service;

class ServiceReference extends ReferenceEntry
{
    protected string $model = Service::class;

    protected bool $piniaBindings = false;

    protected int $order = 98;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'parent_id' => ReferenceFieldSchema::make()
                ->hidden(),

            'service_provider_id' => ReferenceFieldSchema::make()
                ->hidden(),

            'service_provider' => ReferenceFieldSchema::make()
                ->label('Провайдер услуг')
                ->required()
                ->visible(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->required()
                ->max(128)
                ->visible(),

            'display_name' => ReferenceFieldSchema::make()
                ->label('Полное наименование')
                ->max(512)
                ->visible(),

            'tags' => ReferenceFieldSchema::make()
                ->label('Тэги')
                ->array(),

            'indicator_code' => ReferenceFieldSchema::make()
                ->hidden(),

            'indicator' => ReferenceFieldSchema::make()
                ->label('Индикатор'),

            'parent' => ReferenceFieldSchema::make()
                ->label('Родитель'),

            'children' => ReferenceFieldSchema::make()
                ->hidden(),
        ];
    }
}
