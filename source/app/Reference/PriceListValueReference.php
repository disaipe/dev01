<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\PriceListValue;
use App\Models\User;

class PriceListValueReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = PriceListValue::class;

    protected string|bool|null $referenceView = false;

    protected string|bool|null $recordView = false;

    protected bool $indicators = false;

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->model)

            ->withKey()

            ->addField('price_list', ReferenceFieldSchema::make()
                ->label('Прайс лист')
                ->required()
                ->pinia(PiniaAttribute::belongsTo('PriceList', 'price_list_id')))

            ->addField('service', ReferenceFieldSchema::make()
                ->label('Услуга')
                ->required()
                ->pinia(PiniaAttribute::belongsTo('Service', 'service_id')))

            ->addField('value', ReferenceFieldSchema::make()
                ->label('Стоимость')
                ->pinia(PiniaAttribute::number()))

            ->toArray();
    }

    public function canRead(User $user = null): bool
    {
        return ! $user?->isClient();
    }
}
