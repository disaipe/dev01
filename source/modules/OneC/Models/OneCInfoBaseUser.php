<?php

namespace App\Modules\OneC\Models;

use App\Core\Reference\ReferenceModel;
use App\Models\Company;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
    const SELECT_SCOPE = 'SELECT_SCOPE';

    protected $fillable = [
        'one_c_info_base_id',
        'username',
        'login',
        'domain',
    ];

    protected $hidden = [
        'ad_user',
    ];

    protected static function booted(): void
    {
        // Some strange magic to append columns from ADUserEntry model.
        // This is required to be able to work with data as with a regular table.
        static::addGlobalScope(static::SELECT_SCOPE, function (Builder $builder) {
            if (Str::startsWith($builder->withoutGlobalScope(static::SELECT_SCOPE)->toSql(), 'select')) {
                /** @var ADUserEntry $usersInstance */
                $usersInstance = app(ADUserEntry::class);
                $usersTable = $usersInstance->getTable();

                $query = $builder->getQuery();
                $bindings = $builder->getBindings();

                $subQuery = $builder->getModel()->newModelQuery()->join(
                    $usersTable,
                    $usersInstance->qualifyColumn('username'),
                    '=',
                    $builder->qualifyColumn('login')
                )
                    ->select($builder->qualifyColumn('*'))
                    ->addSelect($usersInstance->qualifyColumns([
                        'company_prefix',
                    ]));

                $newQuery = DB::table(
                    DB::raw("({$subQuery->toSql()}) as `{$builder->getModel()->getTable()}`")
                );

                $builder
                    ->setQuery($newQuery)
                    ->mergeWheres($query->wheres, $bindings)
                    ->withoutGlobalScope(static::SELECT_SCOPE);

                if ($query->limit) {
                    $builder->limit($query->limit);
                }

                if ($query->offset) {
                    $builder->offset($query->offset);
                }
            }
        });
    }

    public function __construct()
    {
        parent::__construct();

        // add additional filter fields that are attached from
        // the ADUserEntry model
        $this->filterFields = array_merge(
            $this->availableFields(),
            ['company_prefix']
        );
    }

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
        $query->whereHas(
            'ad_user',
            fn (Builder $user) => $user->active()->company($code)
        );
    }
}
