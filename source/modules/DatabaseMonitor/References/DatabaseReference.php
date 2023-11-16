<?php

namespace App\Modules\DatabaseMonitor\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Modules\DatabaseMonitor\Models\Database;

class DatabaseReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = Database::class;

    protected string|bool|null $referenceView = false;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'database_server_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'database_server' => ReferenceFieldSchema::make()
                ->label('Сервер баз данных')
                ->required()
                ->readonly()
                ->pinia(PiniaAttribute::belongsTo('DatabaseServer', 'database_server_id'))
                ->eagerLoad(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->max(128)
                ->required()
                ->readonly()
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'company_code' => ReferenceFieldSchema::make()
                ->label('Код организации')
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'company' => ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_code', 'code'))
                ->eagerLoad(),

            'size' => ReferenceFieldSchema::make()
                ->label('Размер')
                ->readonly()
                ->visible()
                ->pinia(PiniaAttribute::number())
                ->displayFilter('formatKBytes'),

            'updated_at' => ReferenceFieldSchema::make()
                ->label('Дата изменения')
                ->readonly()
                ->visible()
                ->pinia(PiniaAttribute::datetime()),
        ];
    }

    protected function getLabelKey(): string
    {
        return 'dbmon::messages.database';
    }
}
