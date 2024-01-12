<?php

namespace App\Modules\Directum\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\WithoutSoftDeletes;
use App\Models\Company;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property string name
 * @property string domain
 * @property ADUserEntry user
 */
class DirectumUser extends ReferenceModel
{
    use WithoutSoftDeletes;

    const SELECT_SCOPE = 'SELECT_SCOPE';

    protected $fillable = [
        'name',
        'domain',
    ];

    protected $hidden = [
        'user',
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
                    $builder->qualifyColumn('name')
                )
                    ->select($builder->qualifyColumn('*'))
                    ->addSelect($usersInstance->qualifyColumns([
                        'company_prefix',
                        'name as fullname',
                        'post',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(ADUserEntry::class, 'name', 'username');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_prefix', 'code');
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        $query->whereHas('user', fn(Builder $q) => $q->company($code));
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereHas('user', fn(Builder $q) => $q->active());
    }
}
