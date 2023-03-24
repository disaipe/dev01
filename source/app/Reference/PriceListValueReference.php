<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\PriceListValue;

class PriceListValueReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = PriceListValue::class;

    protected string|bool|null $referenceView = false;
    protected string|bool|null $recordView = false;

    protected bool $indicators = false;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'price_list_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'price_list' => ReferenceFieldSchema::make()
                ->label('Прайс лист')
                ->required()
                ->pinia(PiniaAttribute::belongsTo('PriceList', 'price_list_id')),

            'service_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'service' => ReferenceFieldSchema::make()
                ->label('Услуга')
                ->required()
                ->pinia(PiniaAttribute::belongsTo('Service', 'service_id')),

            'value' => ReferenceFieldSchema::make()
                ->label('Стоимость')
                ->pinia(PiniaAttribute::number()),
        ];
    }
}
