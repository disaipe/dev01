<?php

namespace App\Modules\Directum\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\ExtendSelectQuery;
use App\Core\Traits\WithoutSoftDeletes;
use App\Models\Company;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string name
 * @property string domain
 * @property ADUserEntry user
 */
class DirectumUser extends ReferenceModel
{
    use ExtendSelectQuery, WithoutSoftDeletes;

    protected $fillable = [
        'name',
        'domain',
    ];

    protected $hidden = [
        'user',
    ];

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
        $query->where('company_prefix', '=', $code);
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereHas('user', fn (Builder $q) => $q->active());
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
                $builder->qualifyColumn('name')
            )
            ->addSelect($usersInstance->qualifyColumns([
                'company_prefix',
                'name as fullname',
                'post',
            ]));
    }
}
