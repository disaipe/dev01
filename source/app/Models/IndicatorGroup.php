<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\WithoutSoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string name
 * @property string color
 * @property Indicator[] indicators
 */
class IndicatorGroup extends ReferenceModel
{
    use WithoutSoftDeletes;

    protected $fillable = [
        'name',
        'color',
    ];

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class);
    }
}
