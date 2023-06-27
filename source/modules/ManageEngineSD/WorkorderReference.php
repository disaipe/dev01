<?php

namespace App\Modules\ManageEngineSD;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceModel;
use App\Modules\ManageEngineSD\Models\SDWorkorder;

class WorkorderReference extends ReferenceEntry
{
    protected string|ReferenceModel $model = SDWorkorder::class;

    protected string|bool|null $referenceView = false;

    protected function getLabelKey(): string
    {
        return 'mesd::messages.workorder';
    }
}
