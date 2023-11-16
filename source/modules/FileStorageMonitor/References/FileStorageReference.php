<?php

namespace App\Modules\FileStorageMonitor\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\User;
use App\Modules\FileStorageMonitor\Models\FileStorage;

class FileStorageReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = FileStorage::class;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->max(256)
                ->required()
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'company_code' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::string()),

            'company' => ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_code', 'code'))
                ->eagerLoad(),

            'path' => ReferenceFieldSchema::make()
                ->label('Путь к файловому хранилищу')
                ->max(1024)
                ->visible()
                ->required()
                ->pinia(PiniaAttribute::string()),

            'enabled' => ReferenceFieldSchema::make()
                ->label('Активно')
                ->pinia(PiniaAttribute::boolean()),

            'exclude' => ReferenceFieldSchema::make()
                ->label('Не включать в отчет')
                ->pinia(PiniaAttribute::boolean()),

            'size' => ReferenceFieldSchema::make()
                ->label('Размер')
                ->readonly()
                ->visible()
                ->pinia(PiniaAttribute::number())
                ->displayFilter('formatBytes'),

            'last_sync' => ReferenceFieldSchema::make()
                ->label('Дата статуса')
                ->readonly()
                ->pinia(PiniaAttribute::datetime()),

            'last_status' => ReferenceFieldSchema::make()
                ->label('Статус')
                ->readonly()
                ->options([
                    'Q' => 'Ожидание',
                    'R' => 'Успешно',
                    'F' => 'Ошибка',
                ])
                ->pinia(PiniaAttribute::string()),

            'last_duration' => ReferenceFieldSchema::make()
                ->label('Продолжительность')
                ->readonly()
                ->pinia(PiniaAttribute::number()),

            'last_error' => ReferenceFieldSchema::make()
                ->label('Последняя ошибка')
                ->readonly()
                ->pinia(PiniaAttribute::string()),
        ];
    }

    protected function getLabelKey(): string
    {
        return 'fsmonitor::messages.file storage';
    }

    public function canCreate(User $user = null): bool
    {
        return ! $user->isClient();
    }

    public function canUpdate(User $user = null): bool
    {
        return ! $user->isClient();
    }

    public function canDelete(User $user = null): bool
    {
        return ! $user->isClient();
    }
}
