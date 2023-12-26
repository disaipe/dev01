<?php

namespace App\Http\Resources;

use App\Core\Reference\ReferenceModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

/**
 * @property Collection $resource
 */
class ReferenceRecordsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource->count()) {
            return [];
        }

        /** @var ?ReferenceModel $referenceItem */
        $referenceItem = $this->resource->first();

        if ($referenceItem) {
            $relations = $referenceItem->listRelations();
        } else {
            $relations = [];
        }

        return $this->resource
            ->map(fn ($rec) => ReferenceRecordResource::make($rec, $relations))
            ->all();
    }
}
