<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string name
 * @property string fullname
 * @property string identity
 * @property string description
 * @property Collection<Service> services
 */
class ServiceProvider extends ReferenceModel
{
    protected $fillable = [
        'name',
        'fullname',
        'identity',
        'description',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
