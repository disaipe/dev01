<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\PriceList;

class PriceListReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = PriceList::class;

    protected ?string $icon = 'ic:outline-price-change';

    protected int $order = 5;

    protected string|bool|null $referenceView = 'PriceListReference';

    protected string|bool|null $recordView = 'PriceListRecord';

    protected string|null $sidebarMenuParent = null;

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

            'company_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'company' => ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_id')),

            'service_provider_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'service_provider' => ReferenceFieldSchema::make()
                ->label('Провайдер услуг')
                ->required()
                ->visible()
                ->pinia(PiniaAttribute::belongsTo('ServiceProvider', 'service_provider_id'))
                ->eagerLoad(),

            'is_default' => ReferenceFieldSchema::make()
                ->label('Использовать по-умолчанию')
                ->visible()
                ->pinia(PiniaAttribute::boolean()),
        ];
    }

    public function getRecordMeta(): array
    {
        return [
            'scroll' => false,
        ];
    }
}
