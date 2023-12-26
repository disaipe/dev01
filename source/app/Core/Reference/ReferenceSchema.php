<?php

namespace App\Core\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ReferenceSchema
{
    protected array $fields = [];

    protected ?ReferenceModel $modelInstance = null;

    public static function make(): ReferenceSchema
    {
        return new self();
    }

    public function forModel(string|ReferenceModel $model): self
    {
        if (is_string($model)) {
            $this->modelInstance = app($model);
        } else {
            $this->modelInstance = $model;
        }

        return $this;
    }

    public function withKey(string $keyName = 'id'): self
    {
        $modelKey = $this->modelInstance?->getKeyName();

        return $this->addField($modelKey ?? $keyName, ReferenceFieldSchema::make()->id());
    }

    public function addField(string $name, ReferenceFieldSchema $schema): self
    {
        $this->fields[$name] = $schema;

        if ($this->modelInstance?->isRelation($name)) {
            $relation = $this->modelInstance->$name();

            switch (get_class($relation)) {
                case BelongsTo::class:
                    $this->appendRelatedKeyField($relation);
                    break;

                case BelongsToMany::class:
                    $this->appendRelatedKeysField($relation);
                    break;
                default:
                    break;
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->fields;
    }

    protected function appendRelatedKeyField(BelongsTo $relation): void
    {
        $key = $relation->getForeignKeyName();

        $this->addField($key, ReferenceFieldSchema::make()
            ->hidden()
            ->pinia(PiniaAttribute::number())
        );
    }

    protected function appendRelatedKeysField(BelongsToMany $relation): void
    {
        $name = $relation->getRelationName();
        $key = $relation->getRelated()->getKeyName();

        $this->addField("{$name}.{$key}", ReferenceFieldSchema::make()
            ->hidden()
            ->pinia(PiniaAttribute::attr())
        );
    }
}
