<?php

namespace App\Modules\MSExchangeMonitor\Models;

use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MSExchangeMailboxStat extends Model
{
    protected $table = 'ms_exchange_mailbox_stats';

    protected $fillable = [
        'guid',
        'display_name',
        'total_item_size',
        'total_item_count',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(ADUserEntry::class, 'guid', 'mailbox_guid');
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        $query->whereHas('person', function (Builder $personQuery) use ($code) {
            $personQuery->active()->company($code);
        });
    }
}
