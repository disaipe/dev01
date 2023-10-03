<?php

namespace App\Modules\Directum\Models;

use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string name
 * @property string domain
 */
class DirectumUser extends Model
{
    protected $fillable = [
        'name',
        'domain',
    ];

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
