<?php

namespace App\Core;

use App\Core\Traits\Protocolable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferenceModel extends Model
{
    use SoftDeletes, Protocolable;
}
