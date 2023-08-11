<?php

namespace App\Modules\ActiveDirectory;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\User;
use App\Modules\ActiveDirectory\Models\ADEntry;

class ADEntryReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = ADEntry::class;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'company_prefix' => ReferenceFieldSchema::make()
                ->label('Префикс организации')
                ->pinia(PiniaAttribute::string()),

            'company_name' => ReferenceFieldSchema::make()
                ->label('Организация')
                ->pinia(PiniaAttribute::string())
                ->visible(),

            'username' => ReferenceFieldSchema::make()
                ->label('Логин')
                ->pinia(PiniaAttribute::string())
                ->visible(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->pinia(PiniaAttribute::string())
                ->visible(),

            'department' => ReferenceFieldSchema::make()
                ->label('Подразделение')
                ->pinia(PiniaAttribute::string())
                ->visible(),

            'post' => ReferenceFieldSchema::make()
                ->label('Должность')
                ->pinia(PiniaAttribute::string())
                ->visible(),

            'email' => ReferenceFieldSchema::make()
                ->label('E-mail')
                ->pinia(PiniaAttribute::string())
                ->visible(),

            'ou_path' => ReferenceFieldSchema::make()
                ->label('OU')
                ->pinia(PiniaAttribute::string()),

            'groups' => ReferenceFieldSchema::make()
                ->label('Группы')
                ->pinia(PiniaAttribute::attr([])),

            'last_logon' => ReferenceFieldSchema::make()
                ->label('Последний вход')
                ->pinia(PiniaAttribute::string()),

            'logon_count' => ReferenceFieldSchema::make()
                ->label('Кол-во входов')
                ->pinia(PiniaAttribute::number()),

            'state' => ReferenceFieldSchema::make()
                ->label('Состояние')
                ->pinia(PiniaAttribute::number()),

            'sip_enabled' => ReferenceFieldSchema::make()
                ->label('Lync')
                ->pinia(PiniaAttribute::boolean()),

            'blocked' => ReferenceFieldSchema::make()
                ->label('Заблокирован')
                ->pinia(PiniaAttribute::boolean())
                ->visible(),
        ];
    }

    public function canCreate(User $user = null): bool
    {
        return false;
    }

    public function canUpdate(User $user = null): bool
    {
        return false;
    }

    public function canDelete(User $user = null): bool
    {
        return false;
    }

    protected function getLabelKey(): string
    {
        return 'ad::messages.ad_entry';
    }
}
