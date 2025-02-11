<?php

namespace App\Http\Resources;

use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ReferenceModel $resource
 */
class ReferenceRecordResource extends JsonResource
{
    protected array $relations;

    public function __construct($resource, ?array $relations = null)
    {
        parent::__construct($resource);

        if ($relations) {
            $this->relations = $relations;
        } else {
            $this->relations = $this->resource->listRelations();
        }
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $relations = $this->relations;

        foreach ($relations as $name => $type) {
            switch ($type) {
                case BelongsToMany::class:
                    $this->appendRelatedKeysField($name);
                    break;
                default:
                    break;
            }
        }

        return array_merge(parent::toArray($request), $this->additional);
    }

    /**
     * Appends additional array field with the relation
     * related record keys.
     */
    protected function appendRelatedKeysField(string $relationName): void
    {
        $relation = $this->resource->$relationName();
        $keyName = $relation->getRelated()->getKeyName();
        $this->additional["{$relationName}.{$keyName}"] = $this->resource->$relationName()->pluck($keyName)->toArray();
    }
}
