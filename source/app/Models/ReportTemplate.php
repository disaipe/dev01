<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string name
 * @property int service_provider_id
 * @property mixed data
 *
 * @property ServiceProvider serviceProvider
 */
class ReportTemplate extends ReferenceModel
{
    protected $fillable = [
        'name',
        'service_provider_id',
        'content'
    ];

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }
}
