<?php

namespace App\Models;

use App\Core\Enums\ProtocolRecordAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string datetime
 * @property int user_id
 * @property ProtocolRecordAction action
 * @property int object_id
 * @property string object_type
 * @property mixed data
 */
class ProtocolRecord extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'object_id',
        'object_type',
        'data',
    ];

    protected $casts = [
        'action' => ProtocolRecordAction::class,
        'data' => 'json',
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
