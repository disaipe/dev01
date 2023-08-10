<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\ReportTemplate;
use App\Models\User;

class ReportTemplateReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = ReportTemplate::class;

    protected ?string $icon = 'tabler:template';

    protected int $order = 6;

    protected string|bool|null $referenceView = 'ReportTemplateReference';

    protected string|bool|null $recordView = 'ReportTemplateRecord';

    protected string|null $sidebarMenuParent = null;

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

    public function canRead(User $user = null): bool
    {
        return ! $user?->isClient();
    }
}
