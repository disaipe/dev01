<?php

namespace App\Modules\ManageEngineSD\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Modules\ManageEngineSD\Models\SDWorkorder;

class WorkorderReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = SDWorkorder::class;

    protected string|bool|null $referenceView = false;

    protected function getLabelKey(): string
    {
        return 'mesd::messages.workorder';
    }

    public function getSchema(): array
    {
        return [
            'workorderid' => ReferenceFieldSchema::make()
                ->id()
                ->label('ID заявки')
                ->visible(),

            'title' => ReferenceFieldSchema::make()
                ->label('Заголовок')
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'description' => ReferenceFieldSchema::make()
                ->label('Описание')
                ->pinia(PiniaAttribute::string()),

            'hours' => ReferenceFieldSchema::make()
                ->label('Затрачено времени')
                ->visible()
                ->pinia(PiniaAttribute::number())
        ];
    }
}
