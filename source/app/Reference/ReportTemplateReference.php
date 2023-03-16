<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Models\ReportTemplate;

class ReportTemplateReference extends ReferenceEntry
{
    protected string $model = ReportTemplate::class;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->required()
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'service_provider' => ReferenceFieldSchema::make()
                ->label('Провайдер услуг')
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('ServiceProvider', 'service_provider_id')),

            'service_provider_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'content' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::attr()),
        ];
    }

    public function getRecordMeta(): array
    {
        return [
            'scroll' => false,
        ];
    }
}
