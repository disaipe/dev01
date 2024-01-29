<?php

namespace App\Modules\OneC\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\User;
use App\Modules\OneC\Models\OneCDomainUser;
use Illuminate\Database\Eloquent\Builder;

class OneCDomainUserReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = OneCDomainUser::class;

    protected string|bool|null $recordView = 'OneCDomainUserRecord';

    protected ?string $primaryDisplayField = 'username';

    protected ?string $icon = 'tabler:users';

    protected function getLabelKey(): string
    {
        return 'onec::messages.domain user';
    }

    public function query(): Builder
    {
        return parent::query()->distinct();
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

            'company_prefix' => ReferenceFieldSchema::make()
                ->label('Код организации')
                ->pinia(PiniaAttribute::string()),

            'company' => ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_prefix', 'code'))
                ->eagerLoad(),

            'info_base_count' => ReferenceFieldSchema::make()
                ->label('Кол-во баз')
                ->visible()
                ->pinia(PiniaAttribute::number()),

            'blocked' => ReferenceFieldSchema::make()
                ->label('Заблокирован')
                ->visible()
                ->pinia(PiniaAttribute::boolean()),
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
}
