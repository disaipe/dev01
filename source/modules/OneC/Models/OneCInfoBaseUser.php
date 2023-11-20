<?php

namespace App\Modules\OneC\Models;

use App\Core\Reference\ReferenceModel;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int one_c_info_base_id
 * @property string username
 * @property string login
 * @property string domain
 * @property string company_prefix
 * @property ADUserEntry ad_user
 */
class OneCInfoBaseUser extends ReferenceModel
{
    protected $fillable = [
        'one_c_info_base_id',
        'username',
        'login',
        'domain',
    ];

    protected $hidden = [
        'ad_user',
    ];

    public function newQuery(): Builder|QueryBuilder
    {
        /** @var ADUserEntry $usersInstance */
        $usersInstance = app(ADUserEntry::class);
        $usersTable = $usersInstance->getTable();

        return parent::newQuery()->leftJoin(
            $usersTable,
            $usersInstance->qualifyColumn('username'),
            '=',
            $this->qualifyColumn('login')
        )
            ->select($this->qualifyColumn('*'))
            ->addSelect($usersInstance->qualifyColumns([
                'company_prefix',
            ]));
    }

    public function ad_user(): BelongsTo
    {
        return $this->belongsTo(ADUserEntry::class, 'login', 'username');
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        $query->whereHas(
            'ad_user',
            fn (Builder $user) => $user->active()->company($code)
        );
    }
}
