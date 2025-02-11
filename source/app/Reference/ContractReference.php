<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\Contract;
use App\Models\User;

class ContractReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = Contract::class;

    protected ?string $primaryDisplayField = 'number';

    protected ?string $icon = 'teenyicons:contract-outline';

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->model)

            ->withKey()

            ->addField('number', ReferenceFieldSchema::make()
                ->label('Номер договора')
                ->required()
                ->max(64)
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('date', ReferenceFieldSchema::make()
                ->label('Дата договора')
                ->visible()
                ->pinia(PiniaAttribute::date()))

            ->addField('company', ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->required()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_id')))

            ->addField('service_provider', ReferenceFieldSchema::make()
                ->label('Провайдер услуг')
                ->visible()
                ->required()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('ServiceProvider', 'service_provider_id')))

            ->addField('description', ReferenceFieldSchema::make()
                ->label('Описание')
                ->max(2048)
                ->textarea()
                ->pinia(PiniaAttribute::string()))

            ->addField('is_actual', ReferenceFieldSchema::make()
                ->label('Актуальный')
                ->visible()
                ->pinia(PiniaAttribute::boolean()))

            ->toArray();
    }

    public function canUpdate(?User $user = null): bool
    {
        return ! $user?->isClient();
    }
}
