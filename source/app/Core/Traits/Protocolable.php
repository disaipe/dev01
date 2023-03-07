<?php

namespace App\Core\Traits;

use App\Events\ReferenceCreatedEvent;
use App\Events\ReferenceForceDeletedEvent;
use App\Events\ReferenceRestoredEvent;
use App\Events\ReferenceSoftDeletedEvent;
use App\Events\ReferenceUpdatedEvent;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Protocolable
{
    public static function bootProtocolable(): void
    {
        $model = static::class;
        $traits = class_uses(static::class);
        $softDeletes = in_array(SoftDeletes::class, $traits);

        $events = [
            'created' => ReferenceCreatedEvent::class,
            'updated' => ReferenceUpdatedEvent::class,
        ];

        if ($softDeletes) {
            $events['softDeleted'] = ReferenceSoftDeletedEvent::class;
            $events['forceDeleted'] = ReferenceForceDeletedEvent::class;
            $events['restored'] = ReferenceRestoredEvent::class;
        } else {
            $events['deleted'] = ReferenceSoftDeletedEvent::class;
        }

        foreach ($events as $event => $eventHandler) {
            if (method_exists(self::class, $event)) {
                self::$event(fn ($record) => $eventHandler::dispatch($record));
            }
        }
    }
}
