<?php

namespace App\Modules\OneC\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\User;
use App\Modules\OneC\Models\OneCInfoBaseUser;

class OneCInfoBaseUserReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = OneCInfoBaseUser::class;

    protected string|bool|null $referenceView = false;

    protected function getLabelKey(): string
    {
        return 'onec::messages.info base user';
    }

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'one_c_info_base_id' => ReferenceFieldSchema::make()
                ->hidden()
                ->pinia(PiniaAttribute::number()),

            'username' => ReferenceFieldSchema::make()
                ->label('Имя пользователя')
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'login' => ReferenceFieldSchema::make()
                ->label('Логин')
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'domain' => ReferenceFieldSchema::make()
                ->label('Домен')
                ->pinia(PiniaAttribute::string()),

            'company_prefix' => ReferenceFieldSchema::make()
                ->label('Код организации')
                ->pinia(PiniaAttribute::string()),

            'company' => ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_prefix', 'code'))
                ->eagerLoad(),

            'allow_login' => ReferenceFieldSchema::make()
                ->label('Вход разрешен')
                ->visible()
                ->pinia(PiniaAttribute::boolean()),
        ];
    }

    public function canCreate(?User $user = null): bool
    {
        return false;
    }

    public function canUpdate(?User $user = null): bool
    {
        return false;
    }

    public function canDelete(?User $user = null): bool
    {
        return false;
    }
}
