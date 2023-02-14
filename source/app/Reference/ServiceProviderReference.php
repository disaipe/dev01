<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Models\ServiceProvider;

class ServiceProviderReference extends ReferenceEntry
{
    protected string $model = ServiceProvider::class;

    protected bool $piniaBindings = false;

    protected array $schema = [
        'id' => [
            'label' => 'ID',
        ],
        'name' => [
            'label' => 'Наименование',
            'rules' => 'required',
            'defaultColumn' => true,
        ],
        'fullname' => [
            'label' => 'Полное наименование',
        ],
        'identity' => [
            'label' => 'ИНН',
            'rules' => 'max:32',
            'defaultColumn' => true,
        ],
        'description' => [
            'label' => 'Описание',
        ],
    ];
}
