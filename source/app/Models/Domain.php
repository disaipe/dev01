<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property string code
 * @property string host
 * @property numeric port
 * @property string username
 * @property string password
 * @property string base_dn
 * @property numeric timeout
 * @property bool ssl
 * @property bool tls
 * @property bool enabled
 */
class Domain extends Model
{
    protected $fillable = [
        'name',
        'code',
        'host',
        'port',
        'username',
        'password',
        'base_dn',
        'timeout',
        'ssl',
        'tls',
        'enabled',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'ssl' => 'boolean',
        'tls' => 'boolean',
        'enabled' => 'boolean',
    ];
}
