<?php

namespace App\Modules\Atlanta\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\User;
use App\Modules\Atlanta\Models\AtlantaUser;

class AtlantaUserReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = AtlantaUser::class;

    protected ?string $sidebarMenuParent = 'atlanta';

    protected function getLabelKey(): string
    {
        return 'atlanta::messages.user';
    }

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->getModel())

            ->withKey()

            ->addField('username', ReferenceFieldSchema::make()
                ->label('Логин')
                ->pinia(PiniaAttribute::string()))

            ->addField('name', ReferenceFieldSchema::make()
                ->label('Наименование')
                ->visible()
                ->pinia(PiniaAttribute::string()))

            ->addField('company', ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_prefix', 'code')))

            ->addField('department', ReferenceFieldSchema::make()
                ->label('Подразделение')
                ->pinia(PiniaAttribute::string()))

            ->addField('post', ReferenceFieldSchema::make()
                ->label('Должность')
                ->pinia(PiniaAttribute::string()))

            ->addField('has_rdp', ReferenceFieldSchema::make()
                ->label('RDP')
                ->checkbox()
                ->pinia(PiniaAttribute::boolean()))

            ->addField('has_sip', ReferenceFieldSchema::make()
                ->label('Lync')
                ->checkbox()
                ->pinia(PiniaAttribute::boolean()))

            ->addField('has_vpn', ReferenceFieldSchema::make()
                ->label('VPN')
                ->checkbox()
                ->pinia(PiniaAttribute::boolean()))

            ->addField('has_directum', ReferenceFieldSchema::make()
                ->label('Directum')
                ->checkbox()
                ->pinia(PiniaAttribute::boolean()))

            ->addField('has_onec', ReferenceFieldSchema::make()
                ->label('1С')
                ->checkbox()
                ->pinia(PiniaAttribute::boolean()))

            ->toArray();
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
}
