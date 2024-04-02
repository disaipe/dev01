<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\Company;

class CompanyReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = Company::class;

    protected ?string $primaryDisplayField = 'name';

    protected ?string $icon = 'octicon:organization-16';

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->model)

            ->withKey()

            ->addField('code', ReferenceFieldSchema::make()
                ->label(__('admin.code'))
                ->required()
                ->max(16)
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('name', ReferenceFieldSchema::make()
                ->label(__('admin.name'))
                ->required()
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('fullname', ReferenceFieldSchema::make()
                ->label(__('reference.$company.fullname.label'))
                ->max(512)
                ->pinia(PiniaAttribute::string()))

            ->addField('identity', ReferenceFieldSchema::make()
                ->label(__('reference.$company.identity.label'))
                ->max(32)
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('description', ReferenceFieldSchema::make()
                ->label(__('reference.$company.description.label'))
                ->pinia(PiniaAttribute::string()))

            ->toArray();
    }
}
