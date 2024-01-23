<?php

namespace App\Modules\OneC\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\WithoutSoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function domain_users(): BelongsToMany
    {
        return $this->belongsToMany(
            OneCDomainUser::class,
            'one_c_user_info_base',
            'one_c_info_base_id',
            'one_c_domain_user_id'
        );
    }
}
