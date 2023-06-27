<?php

namespace App\Modules\ManageEngineSD\Models;

use App\Modules\ManageEngineSD\SDConnection;
use Illuminate\Database\Eloquent\Model;

class SDWorkorderState extends Model
{
    protected $connection = SDConnection::NAME;
    protected $table = 'workorderstates';
    protected $primaryKey = 'workorderid';
}
