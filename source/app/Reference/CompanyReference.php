<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Models\Company;

class CompanyReference extends ReferenceEntry
{
    protected string $model = Company::class;

    protected bool $piniaBindings = false;

    protected array $schema = [
        'id' => [
            'label' => 'ID',
        ],
        'code' => [
            'label' => 'Префикс',
            'rules' => 'required|max:16',
            'defaultColumn' => true,
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
