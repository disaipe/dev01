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
            'hidden' => true,
        ],
        'parent_id' => [
            'hidden' => true,
        ],
        'name' => [
            'label' => 'Наименование',
            'rules' => 'required|max:128',
            'visible' => true,
        ],
        'display_name' => [
            'rules' => 'max:512',
            'label' => 'Полное наименование',
            'visible' => true,
        ],
        'tags' => [
            'rules' => 'array',
            'label' => 'Тэги',
        ],
        'indicator_code' => [
            'hidden' => true
        ],
        'indicator' => [
            'label' => 'Индикатор'
        ],
        'parent' => [
            'label' => 'Родитель',
        ],
        'children' => [
            'hidden' => true,
        ],
    ];
}
