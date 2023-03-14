<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Models\PriceList;

class PriceListReference extends ReferenceEntry
{
    protected string $model = PriceList::class;

    protected ?string $icon = 'ic:outline-price-change';

    protected int $order = 97;

    protected ?string $referenceView = 'PriceListReference';

    protected ?string $recordView = 'PriceListRecord';

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->visible()
                ->required()
                ->max(128)
                ->pinia(PiniaAttribute::string()),

            'service_provider_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'service_provider' => ReferenceFieldSchema::make()
                ->label('Провайдер услуг')
                ->required()
                ->visible()
                ->pinia(PiniaAttribute::belongsTo('ServiceProvider', 'service_provider_id'))
                ->eagerLoad(),

            'isDefault' => ReferenceFieldSchema::make()
                ->label('Использовать по-умолчанию')
                ->visible()
                ->pinia(PiniaAttribute::boolean())
        ];
    }

    public function getRecordMeta(): array
    {
        return [
            'scroll' => false
        ];
    }
}
