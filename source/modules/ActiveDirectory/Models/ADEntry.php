<?php

namespace App\Modules\ActiveDirectory\Models;

use App\Core\ReferenceModel;
use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ADEntry extends ReferenceModel
{
    protected $table = 'ad_entries';

    protected $fillable = [
        'company_prefix',
        'company_name',
        'username',
        'name',
        'department',
        'post',
        'email',
        'ou_path',
        'groups',
        'last_logon',
        'logon_count',
        'state',
        'sip_enabled',
        'blocked',
    ];

    protected $casts = [
        'groups' => 'array',
        'last_logon' => 'datetime:Y-m-d H:i:s',
        'sip_enabled' => 'boolean',
        'blocked' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class,
            'company_prefix',
            'code'
        );
    }
}
