<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\Contract;

class ContractReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = Contract::class;

    protected int $order = 99;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'number' => ReferenceFieldSchema::make()
                ->label('Номер договора')
                ->required()
                ->max(64)
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'date' => ReferenceFieldSchema::make()
                ->label('Дата договора')
                ->visible()
                ->pinia(PiniaAttribute::date()),

            'company_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'company' => ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->required()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_id')),

            'service_provider_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'service_provider' => ReferenceFieldSchema::make()
                ->label('Провайдер услуг')
                ->visible()
                ->required()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('ServiceProvider', 'service_provider_id')),

            'description' => ReferenceFieldSchema::make()
                ->label('Описание')
                ->max(2048)
                ->textarea()
                ->pinia(PiniaAttribute::string()),

            'is_actual' => ReferenceFieldSchema::make()
                ->label('Актуальный')
                ->visible()
                ->pinia(PiniaAttribute::boolean()),
        ];
    }
}
