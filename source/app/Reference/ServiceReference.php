<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
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

    public function canRead(User $user = null): bool
    {
        return ! $user?->isClient();
    }
}
