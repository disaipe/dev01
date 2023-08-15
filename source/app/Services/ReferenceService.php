<?php

namespace App\Services;

use App\Core\Enums\CustomReferenceContextType;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceManager;
use App\Facades\Auth;
use App\Models\CustomReference;
use App\Models\Reference;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ReferenceService
{
    public static array $modelClasses = [];

    /**
     * Get reference model from custom reference
     */
    public static function getModelFromCustom(CustomReference $customReference): Reference
    {
        $key = $customReference->getKey();

        if (Arr::has(static::$modelClasses, $key)) {
            return Arr::get(static::$modelClasses, $key);
        }

        $tableName = CustomReferenceTableService::getTableName($customReference->name);
        $companyContext = ($customReference->company_context ? 'true' : 'false');
        $contextType = $customReference->context_type ?? CustomReferenceContextType::Id->value;

        /** @var Reference $instance */
        $instance = null;

        eval('$instance = new class() extends \App\Models\Reference
        {
            public function getTable()
            {
                return "'.$tableName.'";
            }

            public function hasCompanyContext(): bool {
                return '.$companyContext.';
            }

            public function getContextType(): \App\Core\Enums\CustomReferenceContextType
            {
                return \App\Core\Enums\CustomReferenceContextType::from("'.$contextType.'");
            }
        };');

        static::$modelClasses[$key] = $instance;

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
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        /** @var ReferenceManager $references */
        $references = app('references');

        $routes = Arr::map($references->getReferences(), function (ReferenceEntry $entry) use ($user) {
            if (! $entry->canRead($user)) {
                return null;
            }

            $model = $entry->getName();
            $referenceView = $entry->getReferenceView();
            $recordView = $entry->getRecordView();

            $meta = [
                'model' => $model,
                'order' => $entry->getOrder(),
                'icon' => $entry->getIcon(),
                'permissions' => [
                    'create' => $entry->canCreate($user),
                    'update' => $entry->canUpdate($user),
                    'delete' => $entry->canDelete($user),
                ],
                'view' => $referenceView,
                'recordView' => $recordView,
                'menuParent' => $entry->getSidebarMenuParent(),
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
                    'menuParent' => $entry->getSidebarMenuParent(),
                ],
                'children' => $routes,
            ];
        });

        if (! $user->isClient()) {
            $routes[] = [
                'name' => 'report-invoice',
                'path' => 'invoice',
                'meta' => [
                    'title' => 'Отчет',
                    'view' => 'InvoiceReport',
                    'icon' => 'teenyicons:invoice-outline',
                    'order' => 2,
                    'scroll' => false,
                ],
            ];
        }

        return array_values(Arr::whereNotNull($routes));
    }
}
