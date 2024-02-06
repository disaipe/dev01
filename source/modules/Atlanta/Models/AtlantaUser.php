<?php

namespace App\Modules\Atlanta\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\CompanyScope;
use App\Core\Traits\WithoutSoftDeletes;
use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtlantaUser extends ReferenceModel
{
    use CompanyScope, WithoutSoftDeletes;

    protected ?string $companyCodeColumn = 'company_prefix';

    protected $table = 'atlanta_users_summary';

    protected $casts = [
        'has_vpn' => 'bool',
        'has_rdp' => 'bool',
        'has_sip' => 'bool',
        'has_directum' => 'bool',
        'has_onec' => 'bool',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_prefix', 'code');
    }
}
