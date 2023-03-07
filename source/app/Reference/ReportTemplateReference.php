<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Models\ReportTemplate;

class ReportTemplateReference extends ReferenceEntry
{
    protected string $model = ReportTemplate::class;

    protected bool $piniaBindings = false;

    protected ?string $view = 'ReportTemplate';

    protected array $schema = [
        'id' => [
            'label' => 'ID',
        ],
        'name' => [
            'label' => 'Наименование',
            'rules' => 'required',
            'defaultColumn' => true,
        ],
        'service_provider' => [
            'label' => 'Провайдер услуг',
        ],
        'service_provider_id' => [
            'visible' => false,
        ],
        'content' => [
            'visible' => false,
        ]
    ];
}