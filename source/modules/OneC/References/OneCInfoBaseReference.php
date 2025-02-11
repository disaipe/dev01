<?php

namespace App\Modules\OneC\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Models\User;
use App\Modules\OneC\Models\OneCInfoBase;

class OneCInfoBaseReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = OneCInfoBase::class;

    protected string|bool|null $recordView = 'OneCInfoBaseRecord';

    protected ?string $sidebarMenuParent = 'onec';

    protected ?string $icon = 'file-icons:1c';

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

            'company_code' => ReferenceFieldSchema::make()
                ->label('Код организации')
                ->pinia(PiniaAttribute::string()),

            'company' => ReferenceFieldSchema::make()
                ->label('Организация')
                ->visible()
                ->eagerLoad()
                ->pinia(PiniaAttribute::belongsTo('Company', 'company_code', 'code')),

            'ref' => ReferenceFieldSchema::make()
                ->label('Имя базы данных')
                ->visible()
                ->pinia(PiniaAttribute::string()),
        ];
    }

    protected function getLabelKey(): string
    {
        return 'onec::messages.info base';
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
