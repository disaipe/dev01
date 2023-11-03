<?php

namespace App\Modules\DatabaseMonitor\Models;

use App\Core\Reference\ReferenceModel;
use App\Modules\DatabaseMonitor\Enums\DatabaseServerStatus;
use App\Support\SqlServerConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string type
 * @property string name
 * @property string host
 * @property string port
 * @property string username
 * @property string password
 * @property string aliases
 * @property bool monitor
 * @property string options
 * @property string last_check
 * @property DatabaseServerStatus last_status
 * @property string last_error
 * @property array databases
 */
class DatabaseServer extends ReferenceModel
{
    protected $fillable = [
        'type',
        'name',
        'host',
        'port',
        'aliases',
        'username',
        'password',
        'monitor',
        'options',
        'last_check',
        'last_status',
        'last_error',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'monitor' => 'boolean',
        'last_status' => DatabaseServerStatus::class,
    ];

    public function databases(): HasMany
    {
        return $this->hasMany(Database::class);
    }

    public function getOptions(): ?array
    {
        return SqlServerConnection::getDriverOptions($this->options);
    }

    /**
     * Scope a query to only include monitored database servers
     */
    public function scopeEnabled(Builder $query): void
    {
        $query->where('monitor', '=', true);
    }
}
