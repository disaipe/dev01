<?php

namespace App\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\Indicator;

class IndicatorReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = Indicator::class;

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->model)

            ->withKey('code')

            ->addField('code', ReferenceFieldSchema::make()
                ->label('Код')
                ->max(256)
                ->required()
                ->pinia(PiniaAttribute::string()))

            ->addField('name', ReferenceFieldSchema::make()
                ->label('Наименование')
                ->max(256)
                ->required()
                ->pinia(PiniaAttribute::string()))

            ->toArray();
    }
}
