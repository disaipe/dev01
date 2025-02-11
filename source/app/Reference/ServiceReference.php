<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\Service;
use App\Models\User;

class ServiceReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = Service::class;

    protected ?string $icon = 'ion:pricetags-outline';

    protected int $order = 4;

    protected ?string $sidebarMenuParent = null;

    protected ?string $primaryDisplayField = 'name';

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->model)

            ->withKey()

            ->addField('parent_id', ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()))

            ->addField('service_provider', ReferenceFieldSchema::make()
                ->label('Провайдер услуг')
                ->required()
                ->visible()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('ServiceProvider', 'service_provider_id')))

            ->addField('name', ReferenceFieldSchema::make()
                ->label('Наименование')
                ->required()
                ->max(128)
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('display_name', ReferenceFieldSchema::make()
                ->label('Полное наименование')
                ->max(512)
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('indicator_code', ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::string()))

            ->addField('indicator', ReferenceFieldSchema::make()
                ->label('Индикатор')
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('Indicator', 'indicator_code', 'code')))

            ->addField('parent', ReferenceFieldSchema::make()
                ->label('Родитель')
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('Service', 'parent_id')))

            ->addField('children', ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::hasMany('Service', 'parent_id')))

            ->toArray();
    }

    public function canRead(?User $user = null): bool
    {
        return ! $user?->isClient();
    }
}
