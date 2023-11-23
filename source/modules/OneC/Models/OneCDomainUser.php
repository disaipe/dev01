<?php

namespace App\Modules\OneC\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\CompanyScope;
use App\Core\Traits\WithoutSoftDeletes;
use App\Models\Company;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Doctrine\DBAL\Query\QueryBuilder;
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

    protected array $sortable = [
        'login',
        'username',
        'info_base_count',
        'blocked',
    ];

    public function newQuery(): Builder|QueryBuilder
    {
        /** @var ADUserEntry $usersInstance */
        $usersInstance = app(ADUserEntry::class);

        /** @var OneCInfoBaseUser $onecInfoBaseUser */
        $onecInfoBaseUser = app(OneCInfoBaseUser::class);

        return parent::newQuery()
            ->withoutGlobalScopes()
            ->fromSub(
                $usersInstance->newQuery()
                    ->joinSub(
                        parent::newQuery()
                            ->distinct()
                            ->select('login')
                            ->selectRaw('count(1) as info_base_count')
                            ->from($onecInfoBaseUser->getTable())
                            ->whereNotNull('login')
                            ->whereNotNull('domain')
                            ->groupBy('login'),
                        $this->getTable(),
                        $usersInstance->qualifyColumn('username'),
                        '=',
                        $this->qualifyColumn('login')
                    )
                    ->select($usersInstance->qualifyColumns([
                        $usersInstance->getKeyName(),
                        'username as login',
                        'name as username',
                        'company_prefix',
                        'blocked',
                    ]))
                    ->addSelect('info_base_count')
                ,
                'one_c_domain_users'
            )
            ->select();
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
