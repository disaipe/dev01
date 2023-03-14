<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int price_list_id
 * @property int service_id
 * @property float value
 * @property PriceList priceList
 * @property Service service
 */
class PriceListValue extends ReferenceModel
{
    protected $fillable = [
        'price_list_id',
        'service_id',
        'value',
    ];

    protected $casts = [
        'value' => 'float',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
