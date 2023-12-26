<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\ServiceProvider;
use App\Models\User;

class ServiceProviderReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = ServiceProvider::class;

    protected ?string $primaryDisplayField = 'name';

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->model)

            ->withKey()

            ->addField('name', ReferenceFieldSchema::make()
                ->label('Наименование')
                ->required()
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('fullname', ReferenceFieldSchema::make()
                ->label('Полное наименование')
                ->pinia(PiniaAttribute::string()))

            ->addField('identity', ReferenceFieldSchema::make()
                ->label('ИНН')
                ->max(32)
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('vat', ReferenceFieldSchema::make()
                ->label('НДС')
                ->visible()
                ->pinia(PiniaAttribute::number()))

            ->addField('description', ReferenceFieldSchema::make()
                ->label('Описание')
                ->pinia(PiniaAttribute::string()))

            ->addField('price_lists', ReferenceFieldSchema::make()
                ->label('Прайс листы')
                ->hidden()
                ->pinia(PiniaAttribute::hasMany('PriceList', 'service_provider_id')))

            ->addField('report_templates', ReferenceFieldSchema::make()
                ->label('Шаблоны отчетов')
                ->hidden()
                ->pinia(PiniaAttribute::hasMany('ReportTemplate', 'service_provider_id')))

            ->toArray();
    }

    public function canRead(User $user = null): bool
    {
        return ! $user?->isClient();
    }
}
