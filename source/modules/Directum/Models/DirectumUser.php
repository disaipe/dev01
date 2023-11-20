<?php

namespace App\Modules\Directum\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\WithoutSoftDeletes;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property string name
 * @property string domain
 * @property ADUserEntry user
 */
class DirectumUser extends ReferenceModel
{
    use WithoutSoftDeletes;

    protected $fillable = [
        'name',
        'domain',
    ];

    protected $hidden = [
        'user',
    ];

    public function newQuery(): Builder|QueryBuilder
    {
        /** @var ADUserEntry $usersInstance */
        $usersInstance = app(ADUserEntry::class);
        $usersTable = $usersInstance->getTable();

        return $this->newModelQuery()->leftJoin(
            $usersTable,
            $usersInstance->qualifyColumn('username'),
            '=',
            $this->qualifyColumn('name')
        )
            ->select($this->qualifyColumn('*'))
            ->addSelect($usersInstance->qualifyColumns([
                'company_prefix',
                'name as fullname',
                'post',
            ]));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(ADUserEntry::class, 'name', 'username');
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        $query->whereHas('user', fn (Builder $q) => $q->company($code));
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereHas('user', fn (Builder $q) => $q->active());
    }
}
