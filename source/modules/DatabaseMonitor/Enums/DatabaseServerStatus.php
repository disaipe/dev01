<?php

namespace App\Modules\DatabaseMonitor\Enums;

enum DatabaseServerStatus: string
{
    case Online = 'O';
    case Offline = 'F';
    case Unknown = 'U';
}
