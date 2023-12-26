<?php

namespace App\Reference;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\Company;

class CompanyReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = Company::class;

    protected bool $piniaBindings = false;

    protected ?string $primaryDisplayField = 'name';

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->model)

            ->withKey()

            ->addField('code', ReferenceFieldSchema::make()
                ->label('Код')
                ->required()
                ->max(16)
                ->visible())

            ->addField('name', ReferenceFieldSchema::make()
                ->label('Наименование')
                ->required()
                ->visible())

            ->addField('fullname', ReferenceFieldSchema::make()
                ->label('Полное наименование')
                ->max(512))

            ->addField('identity', ReferenceFieldSchema::make()
                ->label('ИНН')
                ->max(32)
                ->visible())

            ->addField('description', ReferenceFieldSchema::make()
                ->label('Описание'))

            ->toArray();
    }
}
