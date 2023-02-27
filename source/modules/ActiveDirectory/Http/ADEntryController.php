<?php

namespace App\Modules\ActiveDirectory\Http;

use App\Core\Reference\ReferenceController;
use App\Core\Reference\ReferenceModel;
use App\Modules\ActiveDirectory\Models\ADEntry;

class ADEntryController extends ReferenceController
{
    public string|ReferenceModel $model = ADEntry::class;
}
