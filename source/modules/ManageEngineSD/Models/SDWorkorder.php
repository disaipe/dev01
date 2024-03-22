<?php

namespace App\Modules\ManageEngineSD\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\WithoutSoftDeletes;
use App\Modules\ManageEngineSD\Utils\SDConnection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Kirschbaum\PowerJoins\PowerJoinClause;

/**
 * @property float hours
 */
class SDWorkorder extends ReferenceModel
{
    use WithoutSoftDeletes;

    protected $connection = SDConnection::NAME;

    protected $table = 'workorder';

    protected $primaryKey = 'workorderid';

    public function organization(): BelongsTo
    {
        return $this->belongsTo(SDOrganization::class, 'siteid', 'org_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(SDServiceDefinition::class, 'serviceid', 'serviceid');
    }

    public function charges(): HasManyThrough
    {
        return $this->hasManyThrough(
            SDCharge::class,
            SDWorkorderToCharge::class,
            'workorderid',
            'chargeid',
            'workorderid',
            'chargeid',
        );
    }

    public function status(): HasOneThrough
    {
        return $this->hasOneThrough(
            SDStatusDefinition::class,
            SDWorkorderState::class,
            'workorderid',
            'statusid',
            'workorderid',
            'statusid',
        );
    }

    public function hours(): Attribute
    {
        return Attribute::make(
            get: fn () => round($this->charges()->sum('timespent') / 1000 / 60 / 60, 2)
        );
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        $query
            ->joinRelationship(
                'organization',
                fn (PowerJoinClause $join) => $join->where('sdorganization.description', '=', $code)
            );
    }

    public function scopePeriod(Builder $query, Carbon $from, Carbon $to): void
    {
        $fromStr = $from->format('Y-m-d H:i:s');
        $toStr = $to->format('Y-m-d H:i:s');

        $query
            ->joinRelationship('charges')
            ->whereRaw("TO_TIMESTAMP(chargestable.ts_endtime / 1000) >= '$fromStr'::timestamp")
            ->whereRaw("TO_TIMESTAMP(chargestable.ts_endtime / 1000) <= '$toStr'::timestamp");
    }

    public function scopeCreationPeriod(Builder $query, Carbon $from, Carbon $to)
    {
        $fromStr = $from->format('Y-m-d H:i:s');
        $toStr = $to->format('Y-m-d H:i:s');

        $query
            ->whereRaw("TO_TIMESTAMP(createdtime / 1000) >= '$fromStr'::timestamp")
            ->whereRaw("TO_TIMESTAMP(createdtime / 1000) <= '$toStr'::timestamp");
    }
}
