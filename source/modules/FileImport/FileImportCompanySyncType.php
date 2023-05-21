<?php

namespace App\Modules\FileImport;

enum FileImportCompanySyncType: string
{
    case Id = 'id';
    case Code = 'code';
}
