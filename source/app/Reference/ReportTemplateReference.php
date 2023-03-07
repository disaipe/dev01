<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Models\ReportTemplate;

class ReportTemplateReference extends ReferenceEntry
{
    protected string $model = ReportTemplate::class;

    protected bool $piniaBindings = false;

    protected ?string $referenceView = 'ReportTemplateReference';

    protected ?string $recordView = 'ReportTemplateRecord';

    protected array $schema = [
        'id' => [
            'label' => 'ID',
            'hidden' => true,
        ],
        'name' => [
            'label' => 'Наименование',
            'rules' => 'required',
            'visible' => true,
        ],
        'service_provider' => [
            'label' => 'Провайдер услуг',
        ],
        'service_provider_id' => [
            'hidden' => true,
        ],
        'content' => [
            'hidden' => true,
        ],
    ];

    public function getRecordMeta(): array
    {
        return [
            'scroll' => false,
        ];
    }
}
