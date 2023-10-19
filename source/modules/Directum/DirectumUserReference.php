<?php

namespace App\Modules\Directum;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\User;
use App\Modules\Directum\Models\DirectumUser;

class DirectumUserReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = DirectumUser::class;

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'name' => ReferenceFieldSchema::make()
                ->label('Логин')
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'fullname' => ReferenceFieldSchema::make()
                ->label('Имя')
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'post' => ReferenceFieldSchema::make()
                ->label('Должность')
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'company_prefix' => ReferenceFieldSchema::make()
                ->label('Префикс организации')
                ->pinia(PiniaAttribute::string()),

            'company' => ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_prefix', 'code')),
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
        return 'directum::messages.user entry';
    }
}
