<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Models\Service;

class ServiceReference extends ReferenceEntry
{
    protected string $model = Service::class;

    protected bool $piniaBindings = false;

    protected array $schema = [
        'id' => [
            'label' => 'ID',
        ],
        'parent_id' => [
            'visible' => false,
        ],
        'name' => [
            'label' => 'Наименование',
            'rules' => 'required|max:128',
        ],
        'display_name' => [
            'rules' => 'max:512',
            'label' => 'Полное наименование',
        ],
        'tags' => [
            'rules' => 'array',
            'label' => 'Тэги',
        ],
        'parent' => [
            'label' => 'Родитель',
        ],
        'children' => [
            'visible' => false,
        ],
    ];
}
