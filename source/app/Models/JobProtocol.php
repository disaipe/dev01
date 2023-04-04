<?php

namespace App\Models;

use App\Core\Enums\JobProtocolState;
use Illuminate\Database\Eloquent\Model;

class JobProtocol extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'module',
        'state',
        'result',
        'created_at',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'state' => JobProtocolState::class,
        'result' => 'json',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'started_at' => 'datetime:Y-m-d H:i:s',
        'ended_at' => 'datetime:Y-m-d H:i:s'
    ];

    public $timestamps = false;
}
