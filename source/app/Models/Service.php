<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int parent_id
 * @property string name
 * @property string display_name
 * @property int service_provider_id
 * @property array tags
 * @property string indicator_code
 */
class Service extends ReferenceModel
{
    protected $fillable = [
        'parent_id',
        'name',
        'display_name',
        'service_provider_id',
        'tags',
        'indicator_code',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function service_provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
