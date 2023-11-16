<?php

namespace App\Modules\ActiveDirectory\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\User;
use App\Modules\ActiveDirectory\Models\ADComputerEntry;

class ADComputerEntryReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = ADComputerEntry::class;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->pinia(PiniaAttribute::string())
                ->visible(),

            'dns_name' => ReferenceFieldSchema::make()
                ->label('DNS')
                ->pinia(PiniaAttribute::string())
                ->visible(),

            'operating_system' => ReferenceFieldSchema::make()
                ->label('Система')
                ->pinia(PiniaAttribute::string())
                ->visible(),

            'operating_system_version' => ReferenceFieldSchema::make()
                ->label('Версия системы')
                ->pinia(PiniaAttribute::string()),

            'created_at' => ReferenceFieldSchema::make()
                ->label('Дата создания')
                ->pinia(PiniaAttribute::datetime())
                ->visible(),

            'updated_at' => ReferenceFieldSchema::make()
                ->label('Дата изменения')
                ->pinia(PiniaAttribute::datetime())
                ->visible(),

            'synced_at' => ReferenceFieldSchema::make()
                ->label('Синхронизация')
                ->pinia(PiniaAttribute::datetime())
                ->visible(),

            'ou_path' => ReferenceFieldSchema::make()
                ->label('OU')
                ->pinia(PiniaAttribute::string()),
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
        return 'ad::messages.ad_computer_entry';
    }
}
