<?php

namespace App\Modules\ManageEngineSD\Models;

use App\Modules\ManageEngineSD\Utils\SDConnection;
use Illuminate\Database\Eloquent\Model;

class SDOrganization extends Model
{
    protected $connection = SDConnection::NAME;

    protected $table = 'sdorganization';

    protected $primaryKey = 'org_id';
}
