<?php

namespace App\Events;

use App\Core\Enums\ProtocolRecordAction;
use App\Core\Event\ReferenceEvent;
use App\Facades\Auth;
use App\Models\ProtocolRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ReferenceUpdatedEvent extends ReferenceEvent
{
    protected function protocolEvent(Model $record): void
    {
        $exclude = ['created_at', 'updated_at', 'deleted_at'];

        ProtocolRecord::query()->create([
            'user_id' => Auth::id(),
            'action' => ProtocolRecordAction::Update,
            'object_id' => $record->getKey(),
            'object_type' => class_basename($record),
            'data' => [
                'original' => Arr::except($record->getRawOriginal(), $exclude),
                'changes' => Arr::except($record->getChanges(), $exclude),
            ],
        ]);
    }
}
