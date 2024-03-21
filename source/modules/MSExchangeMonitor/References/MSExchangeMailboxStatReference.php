<?php

namespace App\Modules\MSExchangeMonitor\References;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceModel;
use App\Core\Reference\ReferenceSchema;
use App\Models\User;
use App\Modules\MSExchangeMonitor\Models\MSExchangeMailboxStat;

class MSExchangeMailboxStatReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = MSExchangeMailboxStat::class;

    protected string|bool|null $referenceView = false;

    protected string|bool|null $recordView = false;

    public function getSchema(): array
    {
        return ReferenceSchema::make()
            ->forModel($this->getModel())

            ->withKey()

            ->addField('display_name', ReferenceFieldSchema::make()
                ->label('Наименование')
                ->readonly()
                ->pinia(PiniaAttribute::string()))

            ->addField('total_item_size', ReferenceFieldSchema::make()
                ->label('Размер')
                ->readonly()
                ->pinia(PiniaAttribute::number()))

            ->addField('total_item_count', ReferenceFieldSchema::make()
                ->label('Количество элементов')
                ->readonly()
                ->pinia(PiniaAttribute::number()))

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
