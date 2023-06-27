<?php

namespace App\Modules\ManageEngineSD\Models;

use App\Core\Reference\ReferenceModel;
use App\Modules\ManageEngineSD\SDConnection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * @property float hours
 */
class SDWorkorder extends ReferenceModel
{
    protected $connection = SDConnection::NAME;
    protected $table = 'workorder';
    protected $primaryKey = 'workorderid';

    public static function query(): Builder
    {
        // disable soft delete scope because SD does not have `deleted_at` column
        return parent::query()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(SDOrganization::class, 'siteid', 'org_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(SDServiceDefinition::class, 'serviceid', 'serviceid');
    }

    public function charges(): BelongsToMany
    {
        return $this->belongsToMany(
            SDCharge::class,
            'workordertocharge',
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
            get: fn() => $this->charges()->sum('timespent') / 1000 / 60 / 60
        );
    }

    public function scopeCompany(Builder $query, string $code): Builder
    {
        return $query->whereRelation('organization', 'description', '=', $code);
    }

    public function scopePeriod(Builder $query, Carbon $from, Carbon $to): Builder
    {
        $fromStr = $from->format('Y-m-d H:i:s');
        $toStr = $to->format('Y-m-d H:i:s');

        return $query
            ->whereHas('charges', function (Builder $query) use ($fromStr, $toStr) {
                $query
                    ->whereRaw("TO_TIMESTAMP(ts_endtime / 1000) >= '$fromStr'::timestamp")
                    ->whereRaw("TO_TIMESTAMP(ts_endtime / 1000) <= '$toStr'::timestamp");
            });
    }
}
