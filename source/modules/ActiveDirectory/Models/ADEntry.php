<?php

namespace App\Modules\ActiveDirectory\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\CompanyScope;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ADEntry extends ReferenceModel
{
    use CompanyScope;

    protected $table = 'ad_entries';

    protected ?string $companyCodeColumn = 'company_prefix';

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

    public function scopeActive(Builder $query): void
    {
        $query->where('blocked', 0);
    }
}
