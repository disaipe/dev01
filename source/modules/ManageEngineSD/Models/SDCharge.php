<?php

namespace App\Modules\ManageEngineSD\Models;

use App\Modules\ManageEngineSD\Utils\SDConnection;
use Illuminate\Database\Eloquent\Model;

class SDCharge extends Model
{
    protected $connection = SDConnection::NAME;

    protected $table = 'chargestable';
}
