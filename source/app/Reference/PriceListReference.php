<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\PriceList;
use App\Models\User;

class PriceListReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = PriceList::class;

    protected ?string $icon = 'ic:outline-price-change';

    protected int $order = 5;

    protected string|bool|null $referenceView = 'PriceListReference';

    protected string|bool|null $recordView = 'PriceListRecord';

    protected ?string $sidebarMenuParent = null;

    protected ?string $primaryDisplayField = 'name';

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->model)

            ->withKey()

            ->addField('name', ReferenceFieldSchema::make()
                ->label('Наименование')
                ->visible()
                ->required()
                ->max(128)
                ->pinia(PiniaAttribute::string()))

            ->addField('companies', ReferenceFieldSchema::make()
                ->label('Организации')
                ->visible()
                ->eagerLoad()
                ->pinia(PiniaAttribute::hasManyBy('Company', 'companies.id')))

            ->addField('service_provider', ReferenceFieldSchema::make()
                ->label('Провайдер услуг')
                ->required()
                ->visible()
                ->pinia(PiniaAttribute::belongsTo('ServiceProvider', 'service_provider_id'))
                ->eagerLoad())

            ->addField('is_default', ReferenceFieldSchema::make()
                ->label('Использовать по-умолчанию')
                ->visible()
                ->pinia(PiniaAttribute::boolean()))

            ->toArray();
    }

    public function getRecordMeta(): array
    {
        return [
            'scroll' => false,
        ];
    }

    public function canRead(User $user = null): bool
    {
        return ! $user?->isClient();
    }
}
