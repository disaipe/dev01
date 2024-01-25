<?php

namespace App\Modules\OneC\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\CompanyScope;
use App\Core\Traits\WithoutSoftDeletes;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string username
 * @property string login
 * @property string domain
 * @property string company_prefix
 * @property integer info_base_count
 */
class OneCDomainUser extends ReferenceModel
{
    use CompanyScope, WithoutSoftDeletes;

    protected ?string $companyCodeColumn = 'company_prefix';

    protected $table = 'one_c_domain_users';

    protected $casts = [
        'blocked' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->filterFields = array_merge(
            $this->availableFields(),
            [
                'one_c_domain_user_id',
                'one_c_info_base_user_id',
                'one_c_info_base_id',
            ]
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_prefix', 'code');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('blocked', '=', false);
    }
}
