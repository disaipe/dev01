<?php

namespace App\Modules\DatabaseMonitor;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Modules\DatabaseMonitor\Enums\DatabaseServerStatus;
use App\Modules\DatabaseMonitor\Models\DatabaseServer;

class DatabaseServerReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = DatabaseServer::class;

    protected string|bool|null $recordView = 'DatabaseServerRecord';

    public function getSchema(): array
    {
        $statusOptions = collect(DatabaseServerStatus::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->name])
            ->toArray();

        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'type' => ReferenceFieldSchema::make()
                ->label('Провайдер')
                ->required()
                ->pinia(PiniaAttribute::string())
                ->options([
                    'pdo_mysql' => 'Mysql',
                    'pdo_sqlsrv' => 'SQL Server (2012+)',
                ]),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->max(128)
                ->required()
                ->pinia(PiniaAttribute::string()),

            'host' => ReferenceFieldSchema::make()
                ->label('Хост')
                ->max(64)
                ->required()
                ->pinia(PiniaAttribute::string()),

            'port' => ReferenceFieldSchema::make()
                ->label('Порт')
                ->pinia(PiniaAttribute::number()),

            'username' => ReferenceFieldSchema::make()
                ->label('Имя пользователя')
                ->max(64)
                ->pinia(PiniaAttribute::string()),

            'password' => ReferenceFieldSchema::make()
                ->label('Пароль')
                ->max(64)
                ->pinia(PiniaAttribute::string())
                ->password(),

            'options' => ReferenceFieldSchema::make()
                ->label('Опции драйвера')
                ->max(1024)
                ->pinia(PiniaAttribute::string())
                ->textarea(),

            'monitor' => ReferenceFieldSchema::make()
                ->label('Включено')
                ->pinia(PiniaAttribute::boolean()),

            'last_check' => ReferenceFieldSchema::make()
                ->label('Последняя синхронизация')
                ->readonly()
                ->pinia(PiniaAttribute::datetime()),

            'last_status' => ReferenceFieldSchema::make()
                ->label('Последний статус')
                ->readonly()
                ->pinia(PiniaAttribute::string())
                ->options($statusOptions),

            'last_error' => ReferenceFieldSchema::make()
                ->label('Последняя ошибка')
                ->readonly()
                ->pinia(PiniaAttribute::string())
                ->textarea(),
        ];
    }

    protected function getLabelKey(): string
    {
        return 'dbmon::messages.database server';
    }
}
