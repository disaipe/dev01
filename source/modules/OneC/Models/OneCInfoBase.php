<?php

namespace App\Modules\OneC\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\WithoutSoftDeletes;

/**
 * @property string list_path
 * @property string name
 * @property string conn_string
 * @property string server
 * @property string ref
 * @property string db_type
 * @property string db_server
 * @property string db_base
 */
class OneCInfoBase extends ReferenceModel
{
    use WithoutSoftDeletes;

    protected $fillable = [
        'list_path',
        'name',
        'conn_string',
        'server',
        'ref',
        'db_type',
        'db_server',
        'db_base',
    ];
}
