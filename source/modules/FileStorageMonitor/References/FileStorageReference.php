<?php

namespace App\Modules\FileStorageMonitor\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\User;
use App\Modules\FileStorageMonitor\Models\FileStorage;

class FileStorageReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = FileStorage::class;

    protected ?string $icon = 'tabler:folders';

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->model)

            ->withKey()

            ->addField('name', ReferenceFieldSchema::make()
                ->label('Наименование')
                ->max(256)
                ->required()
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('company', ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_code', 'code'))
                ->eagerLoad())

            ->addField('path', ReferenceFieldSchema::make()
                ->label('Путь к файловому хранилищу')
                ->max(1024)
                ->visible()
                ->required()
                ->pinia(PiniaAttribute::string()))

            ->addField('enabled', ReferenceFieldSchema::make()
                ->label('Активно')
                ->pinia(PiniaAttribute::boolean()))

            ->addField('size', ReferenceFieldSchema::make()
                ->label('Размер')
                ->readonly()
                ->visible()
                ->pinia(PiniaAttribute::number())
                ->displayFilter('formatBytes'))

            ->addField('last_sync', ReferenceFieldSchema::make()
                ->label('Дата статуса')
                ->readonly()
                ->pinia(PiniaAttribute::datetime()))

            ->addField('last_status', ReferenceFieldSchema::make()
                ->label('Статус')
                ->readonly()
                ->options([
                    'Q' => 'Ожидание',
                    'R' => 'Успешно',
                    'F' => 'Ошибка',
                ])
                ->pinia(PiniaAttribute::string()))

            ->addField('last_duration', ReferenceFieldSchema::make()
                ->label('Продолжительность')
                ->readonly()
                ->pinia(PiniaAttribute::number()))

            ->addField('last_error', ReferenceFieldSchema::make()
                ->label('Последняя ошибка')
                ->readonly()
                ->pinia(PiniaAttribute::string()))

            ->toArray();
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
