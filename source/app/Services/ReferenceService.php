<?php

namespace App\Services;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ReferenceService
{
    public function getModels(): array
    {
        /** @var ReferenceManager $references */
        $references = app('references');

        return collect($references->getReferences())
            ->filter(fn (ReferenceEntry $ref) => $ref->hasPiniaBindings())
            ->map(function (ReferenceEntry $entry) {
                $schema = $entry->getSchema();
                $fields = $entry->getPiniaFields();

                $eagerLoad = Arr::where($schema, fn ($field) => $field->isEagerLoad());

                return [
                    'name' => $entry->getName(),
                    'eagerLoad' => array_keys($eagerLoad),
                    'entity' => Str::kebab(Str::plural($entry->getName())),
                    'fields' => $fields,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getVueRoutes(): array
    {
        /** @var ReferenceManager $references */
        $references = app('references');

        $routes = Arr::map($references->getReferences(), function (ReferenceEntry $entry) {
            $model = $entry->getName();

            $meta = [
                'model' => $model,
                'order' => $entry->getOrder(),
                'icon' => $entry->getIcon(),
                'permissions' => [
                    'create' => $entry->canCreate(),
                    'update' => $entry->canUpdate(),
                    'delete' => $entry->canDelete(),
                ],
            ];

            $routes = [];

            // Make reference route if view set
            $referenceView = $entry->getReferenceView();
            if ($referenceView !== false) {
                $routes []= [
                    'name' => $entry->getName().'Reference',
                    'path' => '',
                    'meta' => [
                        ...$meta,
                        'view' => $entry->getReferenceView(),
                        'title' => $entry->getPluralLabel(),
                        'isReference' => true,
                        ...$entry->getReferenceMeta(),
                    ],
                ];
            }

            // Make record route if view set
            $recordView = $entry->getRecordView();
            if ($recordView !== false) {
                $routes []= [
                    'name' => $entry->getName().'Record',
                    'path' => ':id',
                    'meta' => [
                        ...$meta,
                        'view' => $entry->getRecordView(),
                        'title' => $entry->getLabel(),
                        'isRecord' => true,
                        ...$entry->getRecordMeta(),
                    ],
                ];
            }

            if (!count($routes)) {
                return null;
            }

            return [
                'name' => $entry->getName(),
                'path' => $entry->getPrefix(),
                'redirect' => [
                    'name' => $entry->getName().'Reference',
                ],
                'meta' => [
                    'title' => $entry->getPluralLabel(),
                ],
                'children' => $routes,
            ];
        });

        return Arr::whereNotNull($routes);
    }
}
