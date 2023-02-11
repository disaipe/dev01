<?php

namespace App\Modules\ActiveDirectory\Http;

use App\Core\ReferenceController;
use App\Core\ReferenceModel;
use App\Modules\ActiveDirectory\Models\ADEntry;

class ADEntryController extends ReferenceController
{
    public string|ReferenceModel $model = ADEntry::class;
}
