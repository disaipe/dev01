<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Models\Service;

class ServiceReference extends ReferenceEntry
{
    protected string $model = Service::class;

    protected bool $piniaBindings = false;

    protected int $order = 98;

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
            'defaultColumn' => true,
        ],
        'display_name' => [
            'rules' => 'max:512',
            'label' => 'Полное наименование',
            'defaultColumn' => true,
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
