<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string code
 * @property string name
 * @property string fullname
 * @property string identity
 * @property string description
 */
class Company extends ReferenceModel
{
    protected $fillable = [
        'code',
        'name',
        'fullname',
        'identity',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
