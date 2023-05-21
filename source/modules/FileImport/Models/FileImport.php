<?php

namespace App\Modules\FileImport\Models;

use App\Models\CustomReference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string name
 * @property string path
 * @property int custom_reference_id
 * @property array options
 * @property bool enabled
 * @property string last_sync
 * @property CustomReference reference
 */
class FileImport extends Model
{
    protected $fillable = [
        'name',
        'path',
        'custom_reference_id',
        'options',
        'enabled',
        'last_sync',
    ];

    protected $casts = [
        'options' => 'json',
        'enabled' => 'bool',
    ];

    public function reference(): BelongsTo
    {
        return $this->belongsTo(CustomReference::class, 'custom_reference_id');
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('enabled', 1);
    }
}
