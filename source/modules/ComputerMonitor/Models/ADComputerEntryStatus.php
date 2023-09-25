<?php

namespace App\Modules\ComputerMonitor\Models;

use App\Modules\ActiveDirectory\Models\ADComputerEntry;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ADComputerEntryStatus extends Model
{
    protected $table = 'ad_computer_entry_status';

    protected $fillable = [
        'ad_computer_entry_id',
        'username',
        'synced_at',
    ];

    public function computer(): BelongsTo
    {
        return $this->belongsTo(ADComputerEntry::class, 'ad_computer_entry_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(ADUserEntry::class, 'username', 'username');
    }

    public function scopePeriod(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query
            ->where("{$this->getTable()}.synced_at", '>=', $from)
            ->where("{$this->getTable()}.synced_at", '<=', $to);
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        $table = $this->getTable();

        $adUserEntry = new ADUserEntry();
        $adUserTable = $adUserEntry->getTable();
        $adUserCompanyColumn = $adUserEntry->getCompanyColumn();

        $query
            ->select("{$table}.*")
            ->join(
                $adUserTable,
                "{$adUserTable}.username",
                '=',
                "{$table}.username"
            )
            ->where("{$adUserTable}.{$adUserCompanyColumn}", '=', $code);
    }
}
