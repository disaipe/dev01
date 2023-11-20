<?php

namespace App\Modules\OneC\Models;

use App\Core\Reference\ReferenceModel;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string username
 * @property string login
 * @property string domain
 * @property string company_prefix
 * @property integer info_base_count
 */
class OneCDomainUser extends ReferenceModel
{
    protected $table = 'one_c_info_base_users';

    public function newQuery(): Builder|QueryBuilder
    {
        /** @var ADUserEntry $usersInstance */
        $usersInstance = app(ADUserEntry::class);

        return parent::newQuery()
            ->withoutGlobalScopes()
            ->fromSub(
                $usersInstance->newQuery()
                    ->joinSub(
                        parent::newQuery()
                            ->distinct()
                            ->select('login')
                            ->selectRaw('count(1) as info_base_count')
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
                    ]))
                    ->addSelect('info_base_count')
                ,
                'one_c_domain_users'
            )
            ->select();
    }
}
