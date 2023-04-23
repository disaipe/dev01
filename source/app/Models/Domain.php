<?php

namespace App\Models;

use App\Core\Traits\Protocolable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string name
 * @property string code
 * @property string host
 * @property numeric port
 * @property string username
 * @property string password
 * @property string base_dn
 * @property array filters
 * @property numeric timeout
 * @property bool ssl
 * @property bool tls
 * @property bool enabled
 */
class Domain extends Model
{
    use Protocolable;

    protected $fillable = [
        'name',
        'code',
        'host',
        'port',
        'username',
        'password',
        'base_dn',
        'filters',
        'timeout',
        'ssl',
        'tls',
        'enabled',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'filters' => 'json',
        'ssl' => 'boolean',
        'tls' => 'boolean',
        'enabled' => 'boolean',
    ];

    public function scopeEnabled(Builder $query): void
    {
        $query->where('enabled', '=', true);
    }
}
