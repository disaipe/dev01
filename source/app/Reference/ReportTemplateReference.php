<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Models\ReportTemplate;

class ReportTemplateReference extends ReferenceEntry
{
    protected string $model = ReportTemplate::class;

    protected bool $piniaBindings = false;

    protected ?string $referenceView = 'ReportTemplateReference';

    protected ?string $recordView = 'ReportTemplateRecord';

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->required()
                ->visible(),

            'service_provider' => ReferenceFieldSchema::make()
                ->label('Провайдер услуг'),

            'service_provider_id' => ReferenceFieldSchema::make()
                ->hidden(),

            'content' => ReferenceFieldSchema::make()
                ->hidden(),
        ];
    }

    public function getRecordMeta(): array
    {
        return [
            'scroll' => false,
        ];
    }
}
