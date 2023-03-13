<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Models\Company;

class CompanyReference extends ReferenceEntry
{
    protected string $model = Company::class;

    protected bool $piniaBindings = false;

    protected int $order = 99;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'code' => ReferenceFieldSchema::make()
                ->label('Префикс')
                ->required()
                ->max(16)
                ->visible(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->required()
                ->visible(),

            'fullname' => ReferenceFieldSchema::make()
                ->label('Полное наименование')
                ->max(512),

            'identity' => ReferenceFieldSchema::make()
                ->label('ИНН')
                ->max(32)
                ->visible(),

            'description' => ReferenceFieldSchema::make()
                ->label('Описание')
        ];
    }
}
