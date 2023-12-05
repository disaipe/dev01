<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\Company;

class CompanyReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = Company::class;

    protected bool $piniaBindings = false;

    protected ?string $primaryDisplayField = 'name';

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'code' => ReferenceFieldSchema::make()
                ->label('Код')
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
                ->label('Описание'),
        ];
    }
}
