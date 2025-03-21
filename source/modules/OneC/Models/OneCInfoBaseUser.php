<?php

namespace App\Modules\OneC\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\ExtendSelectQuery;
use App\Core\Traits\WithoutSoftDeletes;
use App\Models\Company;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int one_c_info_base_id
 * @property string username
 * @property string login
 * @property string domain
 * @property bool allow_login
 * @property string company_prefix
 * @property ADUserEntry ad_user
 */
class OneCInfoBaseUser extends ReferenceModel
{
    use ExtendSelectQuery, WithoutSoftDeletes;

    protected $fillable = [
        'one_c_info_base_id',
        'username',
        'login',
        'domain',
        'allow_login',
    ];

    protected $hidden = [
        'ad_user',
    ];

    protected $casts = [
        'allow_login' => 'bool',
    ];

    public function ad_user(): BelongsTo
    {
        return $this->belongsTo(ADUserEntry::class, 'login', 'username');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_prefix', 'code');
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        $query->where('company_prefix', '=', $code);
    }

    protected function extendSelect(Builder $builder): Builder
    {
        /** @var ADUserEntry $usersInstance */
        $usersInstance = app(ADUserEntry::class);
        $usersTable = $usersInstance->getTable();

        return $builder
            ->getModel()
            ->newModelQuery()
            ->join(
                $usersTable,
                $usersInstance->qualifyColumn('username'),
                '=',
                $builder->qualifyColumn('login')
            )
            ->addSelect($usersInstance->qualifyColumns([
                'company_prefix',
            ]));
    }
}
