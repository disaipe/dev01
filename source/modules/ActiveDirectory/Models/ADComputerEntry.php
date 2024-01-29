<?php

namespace App\Modules\ActiveDirectory\Models;

use App\Core\Reference\ReferenceModel;

class ADComputerEntry extends ReferenceModel
{
    protected $table = 'ad_computer_entries';

    protected $fillable = [
        'name',
        'ou_path',
        'operating_system',
        'operating_system_version',
        'dns_name',
        'created_at',
        'updated_at',
        'synced_at',
        'deleted_at',
    ];
}
