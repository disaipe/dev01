<?php

namespace App\Modules\DatabaseMonitor\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Modules\DatabaseMonitor\Models\Database;

class DatabaseReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = Database::class;

    protected ?string $icon = 'tabler:database';

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->getModel())

            ->withKey()

            ->addField('database_server', ReferenceFieldSchema::make()
                ->label('Сервер баз данных')
                ->required()
                ->readonly()
                ->pinia(PiniaAttribute::belongsTo('DatabaseServer', 'database_server_id'))
                ->eagerLoad())

            ->addField('name', ReferenceFieldSchema::make()
                ->label('Наименование')
                ->max(128)
                ->required()
                ->readonly()
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('company', ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_code', 'code'))
                ->eagerLoad())

            ->addField('size', ReferenceFieldSchema::make()
                ->label('Размер')
                ->readonly()
                ->visible()
                ->pinia(PiniaAttribute::number())
                ->displayFilter('formatKBytes'))

            ->addField('updated_at', ReferenceFieldSchema::make()
                ->label('Дата изменения')
                ->readonly()
                ->visible()
                ->pinia(PiniaAttribute::datetime()))

            ->toArray();
    }

    protected function getLabelKey(): string
    {
        return 'dbmon::messages.database';
    }
}
