<?php

namespace App\Modules\DatabaseMonitor\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\User;
use App\Modules\DatabaseMonitor\Enums\DatabaseServerStatus;
use App\Modules\DatabaseMonitor\Models\DatabaseServer;

class DatabaseServerReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = DatabaseServer::class;

    protected string|bool|null $recordView = 'DatabaseServerRecord';

    protected ?string $icon = 'tabler:database-cog';

    public function getSchema(): array
    {
        $statusOptions = collect(DatabaseServerStatus::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->name])
            ->toArray();

        return ReferenceSchema::make()
            ->forModel($this->getModel())

            ->withKey()

            ->addField('type', ReferenceFieldSchema::make()
                ->label('Провайдер')
                ->required()
                ->pinia(PiniaAttribute::string())
                ->options([
                    'pdo_mysql' => 'Mysql',
                    'pdo_sqlsrv' => 'SQL Server (2012+)',
                ]))

            ->addField('name', ReferenceFieldSchema::make()
                ->label('Наименование')
                ->max(128)
                ->required()
                ->pinia(PiniaAttribute::string()))

            ->addField('host', ReferenceFieldSchema::make()
                ->label('Хост')
                ->max(64)
                ->required()
                ->pinia(PiniaAttribute::string()))

            ->addField('port', ReferenceFieldSchema::make()
                ->label('Порт')
                ->pinia(PiniaAttribute::number()))

            ->addField('aliases', ReferenceFieldSchema::make()
                ->label('Дополнительные имена хоста')
                ->description(
                    'Укажите дополнительные имена для распознования сервера между различными'
                    .' системами платформы. Например, полное доменное имя и сокращенное...'
                )
                ->pinia(PiniaAttribute::string())
                ->textarea())

            ->addField('username', ReferenceFieldSchema::make()
                ->label('Имя пользователя')
                ->max(64)
                ->pinia(PiniaAttribute::string()))

            ->addField('password', ReferenceFieldSchema::make()
                ->label('Пароль')
                ->max(64)
                ->pinia(PiniaAttribute::string())
                ->password())

            ->addField('options', ReferenceFieldSchema::make()
                ->label('Опции драйвера')
                ->max(1024)
                ->pinia(PiniaAttribute::string())
                ->textarea())

            ->addField('monitor', ReferenceFieldSchema::make()
                ->label('Включено')
                ->pinia(PiniaAttribute::boolean()))

            ->addField('last_check', ReferenceFieldSchema::make()
                ->label('Последняя синхронизация')
                ->readonly()
                ->pinia(PiniaAttribute::datetime()))

            ->addField('last_status', ReferenceFieldSchema::make()
                ->label('Последний статус')
                ->readonly()
                ->pinia(PiniaAttribute::string())
                ->options($statusOptions))

            ->addField('last_error', ReferenceFieldSchema::make()
                ->label('Последняя ошибка')
                ->readonly()
                ->pinia(PiniaAttribute::string())
                ->textarea())

            ->toArray();
    }

    public function canRead(User $user = null): bool
    {
        return !$user->isClient();
    }

    protected function getLabelKey(): string
    {
        return 'dbmon::messages.database server';
    }
}
