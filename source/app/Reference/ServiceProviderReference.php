<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\ServiceProvider;

class ServiceProviderReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = ServiceProvider::class;

    protected int $order = 100;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->required()
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'fullname' => ReferenceFieldSchema::make()
                ->label('Полное наименование')
                ->pinia(PiniaAttribute::string()),

            'identity' => ReferenceFieldSchema::make()
                ->label('ИНН')
                ->max(32)
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'description' => ReferenceFieldSchema::make()
                ->label('Описание')
                ->pinia(PiniaAttribute::string()),

            'priceLists' => ReferenceFieldSchema::make()
                ->label('Прайс листы')
                ->pinia(PiniaAttribute::hasMany('PriceList', 'service_provider_id')),

            'reportTemplates' => ReferenceFieldSchema::make()
                ->label('Шаблоны отчетов')
                ->pinia(PiniaAttribute::hasMany('ReportTemplate', 'service_provider_id')),
        ];
    }
}
