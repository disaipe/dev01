<?php

namespace App\Core\Enums;

enum ProtocolRecordAction: string
{
    case Create = 'C';
    case Update = 'U';
    case Delete = 'D';
    case ForceDelete = 'F';
    case Restore = 'R';
}
