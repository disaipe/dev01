<?php

namespace App\Services;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceManager;
use App\Models\Company;
use App\Models\CustomReference;
use App\Models\Reference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ReferenceService
{
    /**
     * Get reference model from custom reference
     */
    public static function getModelFromCustom(CustomReference $customReference): Reference
    {
        $tableName = CustomReferenceTableService::getTableName($customReference->name);

        $instance = new class($tableName) extends Reference
        {
            public static ?string $referenceTable;

            public static bool $companyContext;

            public function __construct($tableName = null)
            {
                parent::__construct([]);

                $this->setTable($tableName ?? static::$referenceTable);

                static::$referenceTable = $this->getTable();
            }

            public function scopeCompany(Builder $query, string $code): Builder
            {
                if (static::$companyContext) {
                    /** @var Company $company */
                    $company = Company::query()->firstWhere('code', '=', $code);

                    if ($company) {
                        return $query->where('company_id', '=', $company->getKey());
                    }
                }

                return $query;
            }
        };

        $instance::$companyContext = $customReference->company_context;

        return $instance;
    }

    /**
     * Get models array from registered references
     */
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

    /**
     * Generate Vue routes for registered references
     */
    public function getVueRoutes(): array
    {
        /** @var ReferenceManager $references */
        $references = app('references');

        $routes = Arr::map($references->getReferences(), function (ReferenceEntry $entry) {
            $model = $entry->getName();
            $referenceView = $entry->getReferenceView();
            $recordView = $entry->getRecordView();

            $meta = [
                'model' => $model,
                'order' => $entry->getOrder(),
                'icon' => $entry->getIcon(),
                'permissions' => [
                    'create' => $entry->canCreate(),
                    'update' => $entry->canUpdate(),
                    'delete' => $entry->canDelete(),
                ],
                'view' => $referenceView,
                'recordView' => $recordView,
            ];

            $routes = [];

            // Make reference route if view set
            if ($referenceView !== false) {
                $routes[] = [
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
            if ($recordView !== false) {
                $routes[] = [
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

            if (! count($routes)) {
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
