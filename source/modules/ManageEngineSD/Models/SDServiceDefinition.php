<?php

namespace App\Modules\ManageEngineSD\Models;

use App\Modules\ManageEngineSD\SDConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SDServiceDefinition extends Model
{
    protected $connection = SDConnection::NAME;

    protected $table = 'servicedefinition';

    protected $primaryKey = 'serviceid';

    public function scopeActive(Builder $query): void
    {
        $query
            ->where('status', '=', 'ACTIVE')
            ->where('isdisabled', '=', false);
    }
}
