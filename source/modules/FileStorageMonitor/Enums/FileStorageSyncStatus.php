<?php

namespace App\Modules\FileStorageMonitor\Enums;

enum FileStorageSyncStatus: string
{
    case Queued = 'Q';
    case Ready = 'R';
    case Failed = 'F';
}
