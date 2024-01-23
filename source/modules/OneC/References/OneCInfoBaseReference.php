<?php

namespace App\Modules\OneC\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\User;
use App\Modules\OneC\Models\OneCInfoBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class OneCInfoBaseReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = OneCInfoBase::class;

    protected string|bool|null $recordView = 'OneCInfoBaseRecord';

    public function getSchema(): array
    {
        return [
            'id' => ReferenceFieldSchema::make()
                ->id(),

            'name' => ReferenceFieldSchema::make()
                ->label('Наименование')
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'server' => ReferenceFieldSchema::make()
                ->label('Сервер')
                ->visible()
                ->pinia(PiniaAttribute::string()),

            'ref' => ReferenceFieldSchema::make()
                ->label('Имя базы данных')
                ->visible()
                ->pinia(PiniaAttribute::string()),
        ];
    }

    public function makeFilters(): array
    {
        return [
            'one_c_domain_user_id' => function (Builder $query, $value) {
                $query->whereHas('domain_users', fn (Builder $subQuery) => $subQuery->whereKey(Arr::wrap($value)));
            }
        ];
    }

    protected function getLabelKey(): string
    {
        return 'onec::messages.info base';
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
