<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Models\Service;

class ServiceReference extends ReferenceEntry
{
    protected string $model = Service::class;

    protected int $order = 98;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'parent_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'service_provider_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'service_provider' => ReferenceFieldSchema::make()
                ->label('Провайдер услуг')
                ->required()
                ->visible()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('ServiceProvider', 'service_provider_id')),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->required()
                ->max(128)
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'display_name' => ReferenceFieldSchema::make()
                ->label('Полное наименование')
                ->max(512)
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'tags' => ReferenceFieldSchema::make()
                ->label('Тэги')
                ->array()
                ->pinia(PiniaAttribute::attr()),

            'indicator_code' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::string()),

            'indicator' => ReferenceFieldSchema::make()
                ->label('Индикатор')
                ->pinia(PiniaAttribute::belongsTo('Indicator', 'indicator_code', 'code')),

            'parent' => ReferenceFieldSchema::make()
                ->label('Родитель')
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('Service', 'parent_id')),

            'children' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::hasMany('Service', 'parent_id')),
        ];
    }
}
