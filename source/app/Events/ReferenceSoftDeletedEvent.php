<?php

namespace App\Events;

use App\Core\Enums\ProtocolRecordAction;
use App\Core\Event\ReferenceEvent;
use App\Facades\Auth;
use App\Models\ProtocolRecord;
use Illuminate\Database\Eloquent\Model;

class ReferenceSoftDeletedEvent extends ReferenceEvent
{

    protected function protocolEvent(Model $record): void
    {
        ProtocolRecord::query()->create([
            'user_id' => Auth::id(),
            'action' => ProtocolRecordAction::Delete,
            'object_id' => $record->getKey(),
            'object_type' => class_basename($record),
            'data' => $record
        ]);
    }
}
