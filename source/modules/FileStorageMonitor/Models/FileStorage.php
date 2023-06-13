<?php

namespace App\Modules\FileStorageMonitor\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\CompanyScope;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string company_code
 * @property string name
 * @property string path
 * @property bool enabled
 * @property bool exclude
 * @property int size
 * @property string last_sync
 * @property float last_duration
 * @property string last_status
 * @property string last_error
 */
class FileStorage extends ReferenceModel
{
    use CompanyScope;

    protected $fillable = [
        'company_code',
        'name',
        'path',
        'enabled',
        'exclude',
        'size',
        'last_sync',
        'last_duration',
        'last_status',
        'last_error',
    ];

    protected $casts = [
        'enabled' => 'bool',
        'exclude' => 'bool'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class,
            'company_prefix',
            'code'
        );
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('enabled', 1);
    }

    public function scopeReportable(Builder $query): void
    {
        $query->where('exclude', 0);
    }
}
