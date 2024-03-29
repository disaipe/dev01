<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string name
 * @property int service_provider_id
 * @property string content
 * @property ServiceProvider service_provider
 */
class ReportTemplate extends ReferenceModel
{
    protected $fillable = [
        'name',
        'service_provider_id',
        'content',
    ];

    public function service_provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }
}
