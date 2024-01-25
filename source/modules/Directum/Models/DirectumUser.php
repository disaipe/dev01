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

    protected static function booted(): void
    {
        static::extendSelect(function (Builder $builder) {
            /** @var ADUserEntry $usersInstance */
            $usersInstance = app(ADUserEntry::class);
            $usersTable = $usersInstance->getTable();

            return $builder->getModel()->newModelQuery()->join(
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
        });
    }

    public function __construct()
    {
        parent::__construct();

        // add additional filter fields that are attached from
        // the ADUserEntry model
        $this->filterFields = array_merge(
            $this->availableFields(),
            ['company_prefix', 'fullname', 'post']
        );
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
