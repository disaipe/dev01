<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Models\Company;

class CompanyReference extends ReferenceEntry
{
    protected string $model = Company::class;

    protected bool $piniaBindings = false;

    protected int $order = 99;

    protected array $schema = [
        'id' => [
            'label' => 'ID',
            'hidden' => true,
        ],
        'code' => [
            'label' => 'Префикс',
            'rules' => 'required|max:16',
            'visible' => true,
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
