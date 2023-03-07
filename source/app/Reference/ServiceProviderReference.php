<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Models\ServiceProvider;

class ServiceProviderReference extends ReferenceEntry
{
    protected string $model = ServiceProvider::class;

    protected bool $piniaBindings = false;

    protected int $order = 100;

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
        'fullname' => [
            'label' => 'Полное наименование',
        ],
        'identity' => [
            'label' => 'ИНН',
            'rules' => 'max:32',
            'visible' => true,
        ],
        'description' => [
            'label' => 'Описание',
        ],
    ];
}
