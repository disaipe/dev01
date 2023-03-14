<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class HasManySyncMacro
{
    public static function make(): void
    {
        HasMany::macro('sync', function (array $data, $deleting = true) {
            /** @var HasMany $this */
            $changes = [
                'created' => [],
                'deleted' => [],
                'updated' => [],
            ];

            // Get the primary key.
            $relatedKeyName = $this->getRelated()->getKeyName();

            // Get the current key values.
            $current = $this->newQuery()->pluck($relatedKeyName)->all();

            // Cast the given key to an integer if it is numeric.
            $castKey = function ($value) {
                if (is_null($value)) {
                    return null;
                }

                return is_numeric($value) ? (int) $value : (string) $value;
            };

            // Cast the given keys to integers if they are numeric and string otherwise.
            $castKeys = function ($keys) use ($castKey) {
                return (array) array_map(function ($key) use ($castKey) {
                    return $castKey($key);
                }, $keys);
            };

            // Get any non-matching rows.
            $deletedKeys = array_diff($current, $castKeys(
                Arr::pluck($data, $relatedKeyName))
            );

            if ($deleting && count($deletedKeys) > 0) {
                $this->getRelated()->destroy($deletedKeys);
                $changes['deleted'] = $deletedKeys;
            }

            // Separate the submitted data into "update" and "new"
            // We determine "newRows" as those whose $relatedKeyName (usually 'id') is null.
            $newRows = Arr::where($data, fn ($row) => Arr::get($row, $relatedKeyName) === null);

            // We determine "updateRows" as those whose $relatedKeyName (usually 'id') is set, not null.
            $updatedRows = Arr::where($data, fn ($row) => Arr::get($row, $relatedKeyName) !== null);

            if (count($newRows) > 0) {
                $newRecords = $this->createMany($newRows);
                $changes['created'] = $castKeys(
                    $newRecords->pluck($relatedKeyName)->toArray()
                );
            }

            $fillable = $this->getRelated()->getFillable();

            foreach ($updatedRows as $row) {
                $this
                    ->getRelated()
                    ->find($castKey(Arr::get($row, $relatedKeyName)))
                    ->update(Arr::only($row, $fillable));
            }

            $changes['updated'] = $castKeys(Arr::pluck($updatedRows, $relatedKeyName));

            return $changes;
        });
    }
}
