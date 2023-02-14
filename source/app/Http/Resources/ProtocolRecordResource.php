<?php

namespace App\Http\Resources;

use App\Models\ProtocolRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProtocolRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ProtocolRecord $record */
        $record = $this->resource;

        return [
            'datetime' => $record->datetime,
            'action' => __("common.PROTOCOL_ACTION.{$record->action->name}"),
            'user' => $record->user?->name ?? "{$record->user_id} (не найден)",
            'data' => $record->data
        ];
    }
}
