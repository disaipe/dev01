<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\ReportTemplate;
use App\Models\User;

class ReportTemplateReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = ReportTemplate::class;

    protected ?string $icon = 'tabler:template';

    protected int $order = 6;

    protected string|bool|null $referenceView = 'ReportTemplateReference';

    protected string|bool|null $recordView = 'ReportTemplateRecord';

    protected ?string $sidebarMenuParent = null;

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

            ->addField('service_provider', ReferenceFieldSchema::make()
                ->label('Провайдер услуг')
                ->required()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('ServiceProvider', 'service_provider_id')))

            ->addField('content', ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::attr()))

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
