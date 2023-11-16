<?php

namespace App\Modules\ManageEngineSD\Models;

use App\Modules\ManageEngineSD\Utils\SDConnection;
use Illuminate\Database\Eloquent\Model;

class SDStatusDefinition extends Model
{
    protected $connection = SDConnection::NAME;

    protected $table = 'statusdefinition';

    protected $primaryKey = 'statusid';
}
