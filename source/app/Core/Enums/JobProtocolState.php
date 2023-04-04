<?php

namespace App\Core\Enums;

enum JobProtocolState: string
{
    case Create = 'C';
    case Work = 'W';
    case Ready = 'R';
    Case Failed = 'F';
}
