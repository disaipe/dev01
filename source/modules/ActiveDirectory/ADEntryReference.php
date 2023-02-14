<?php

namespace App\Modules\ActiveDirectory;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Modules\ActiveDirectory\Models\ADEntry;

class ADEntryReference extends ReferenceEntry
{
    protected string $model = ADEntry::class;

    public function getSchema(): array
    {
        return [
            'id' => [
                'visible' => false,
                'pinia' => PiniaAttribute::uid(),
            ],
            'company_prefix' => [
                'label' => 'Префикс организации',
                'pinia' => PiniaAttribute::string(),
            ],
            'company_name' => [
                'label' => 'Организация',
                'pinia' => PiniaAttribute::string(),
            ],
            'username' => [
                'label' => 'Логин',
                'pinia' => PiniaAttribute::string(),
            ],
            'name' => [
                'label' => 'Наименование',
                'pinia' => PiniaAttribute::string(),
            ],
            'department' => [
                'label' => 'Подразделение',
                'pinia' => PiniaAttribute::string(),
            ],
            'post' => [
                'label' => 'Должность',
                'pinia' => PiniaAttribute::string(),
            ],
            'email' => [
                'label' => 'E-mail',
                'pinia' => PiniaAttribute::string(),
            ],
            'ou_path' => [
                'label' => 'OU',
                'pinia' => PiniaAttribute::string(),
            ],
            'groups' => [
                'label' => 'Группы',
                'pinia' => PiniaAttribute::attr([]),
            ],
            'last_logon' => [
                'label' => 'Последний вход',
                'pinia' => PiniaAttribute::string(),
            ],
            'logon_count' => [
                'label' => 'Кол-во входов',
                'pinia' => PiniaAttribute::number(),
            ],
            'state' => [
                'label' => 'Состояние',
                'pinia' => PiniaAttribute::number(),
            ],
            'sip_enabled' => [
                'label' => 'Lync',
                'pinia' => PiniaAttribute::boolean(),
            ],
            'blocked' => [
                'label' => 'Заблокирован',
                'pinia' => PiniaAttribute::boolean(),
            ],
        ];
    }

    public function canCreate(): bool
    {
        return false;
    }

    public function canUpdate(): bool
    {
        return false;
    }

    public function canDelete(): bool
    {
        return false;
    }

    protected function getLabelKey(): string
    {
        return 'ad::messages.ad_entry';
    }
}
