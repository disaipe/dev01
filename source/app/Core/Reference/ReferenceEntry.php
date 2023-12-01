<?php

namespace App\Core\Reference;

use App\Core\Enums\CustomReferenceContextType;
use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Models\CustomReference;
use App\Models\User;
use App\Services\ReferenceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class ReferenceEntry
{
    /**
     * Reference linked model
     */
    protected string|ReferenceModel $model;

    /**
     * Route prefix for the controller
     */
    protected ?string $prefix;

    /**
     * Reference name
     */
    protected ?string $name;

    /**
     * Reference label
     */
    protected ?string $label;

    /**
     * Reference plural label
     */
    protected ?string $pluralLabel;

    /**
     * Reference view name for the front-end application
     */
    protected string|bool|null $referenceView;

    /**
     * Record view name for the front-end application
     */
    protected string|bool|null $recordView;

    /**
     * Sidebar menu parent item
     */
    protected ?string $sidebarMenuParent = 'references';

    /**
     * Reference icon
     */
    protected ?string $icon = 'fluent-mdl2:product-catalog';

    /**
     * Menu order
     */
    protected int $order = 0;

    /**
     * Field to sort reference records by default
     */
    protected ?string $primaryDisplayField = null;

    /**
     * Determine the schema contains bindings for the Pinia-orm model
     */
    protected bool $piniaBindings = true;

    /**
     * Indicators can be applied to the reference
     */
    protected bool $indicators = true;

    /**
     * Model fields schema
     */
    protected array $schema = [];

    public function __construct()
    {
        // Autoload model class. Needs to controller works correctly
        if (isset($this->model)) {
            spl_autoload_call($this->getModel());
        }
    }

    /**
     * Create reference entry from custom reference
     */
    public static function fromCustomReference(CustomReference $customReference): self
    {
        $entry = new self();
        $entry->name = Str::ascii(Str::studly($customReference->name));
        $entry->model = ReferenceService::getModelFromCustom($customReference);
        $entry->label = $customReference->label;
        $entry->pluralLabel = $customReference->plural_label;
        $entry->schema = [];

        $fields = Arr::get($customReference->schema, 'fields', []);

        if ($customReference->company_context) {
            if ($customReference->context_type === CustomReferenceContextType::Code->value) {
                $entry->schema['company_code'] = ReferenceFieldSchema::make()
                    ->pinia(PiniaAttribute::string())
                    ->hidden();

                $entry->schema['company'] = ReferenceFieldSchema::make()
                    ->label('Организация')
                    ->required()
                    ->visible()
                    ->eagerLoad()
                    ->pinia(PiniaAttribute::belongsTo('Company', 'company_code', 'code'));
            } else {
                $entry->schema['company_id'] = ReferenceFieldSchema::make()
                    ->pinia(PiniaAttribute::number())
                    ->hidden();

                $entry->schema['company'] = ReferenceFieldSchema::make()
                    ->label('Организация')
                    ->required()
                    ->visible()
                    ->eagerLoad()
                    ->pinia(PiniaAttribute::belongsTo('Company', 'company_id'));
            }
        }

        $hidden = $entry->model->getHidden();

        foreach ($fields as $field) {
            $pk = Arr::get($field, 'pk');
            $name = Arr::get($field, 'name');
            $label = Arr::get($field, 'display_name');
            $type = Arr::get($field, 'type');
            $required = Arr::get($field, 'required');
            $readonly = Arr::get($field, 'readonly');

            if (in_array($name, $hidden)) {
                continue;
            }

            $fieldSchema = ReferenceFieldSchema::make();

            $label && $fieldSchema->label($label);
            $required && $fieldSchema->required();
            $readonly && $fieldSchema->readonly();

            $piniaDefinition = null;

            switch ($type) {
                case 'str':
                case 'string':
                    $piniaDefinition = PiniaAttribute::string();
                    break;
                case 'int':
                case 'integer':
                case 'bigint':
                case 'float':
                    if ($pk) {
                        $piniaDefinition = PiniaAttribute::uid();
                    } else {
                        $piniaDefinition = PiniaAttribute::number();
                    }
                    break;
                case 'bool':
                case 'boolean':
                    $piniaDefinition = PiniaAttribute::boolean();
                    break;
                case 'datetime':
                    $piniaDefinition = PiniaAttribute::datetime();
                    break;
                case 'date':
                    $piniaDefinition = PiniaAttribute::date();
                    break;
                default:
                    $piniaDefinition = PiniaAttribute::attr();
                    break;
            }

            $fieldSchema->pinia($piniaDefinition);

            $entry->schema[$name] = $fieldSchema;
        }

        return $entry;
    }

    /**
     * Get new model query
     */
    public function query(): Builder
    {
        return $this->getModelInstance()->newQuery();
    }

    /**
     * Get reference name
     */
    public function getName(): string
    {
        return $this->name ?? class_basename($this->getModel());
    }

    /**
     * Get reference controller
     */
    public function controller(): ReferenceController
    {
        return ReferenceController::fromReference($this);
    }

    /**
     * Get route path prefix
     */
    public function getPrefix(): string
    {
        if (isset($this->prefix)) {
            return $this->prefix;
        }

        $snaked = Str::snake($this->getName());
        return preg_replace('/(\d+)/', '_$1_' ,$snaked);
    }

    /**
     * Get reference model
     */
    public function getModel(): ReferenceModel|string
    {
        return $this->model;
    }

    /**
     * Get reference model class instance
     */
    public function getModelInstance(): ReferenceModel
    {
        if (is_string($this->model)) {
            return app()->make($this->model);
        }

        return $this->model;
    }

    /**
     * Get model fields schema
     *
     * Used to operate with model fields on front-end
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    /**
     * Returns the model has pinia bindings in field schema
     */
    public function hasPiniaBindings(): bool
    {
        return $this->piniaBindings;
    }

    /**
     * Get fields definition for the Pinia-orm model
     */
    public function getPiniaFields(): ?array
    {
        return Arr::map($this->getSchema(), function ($field) {
            $definition = is_array($field) ? $field : $field->toArray();

            return Arr::get($definition, 'pinia');
        });
    }

    /**
     * Get Vue reference view name.
     *
     * View must be placed in `source/resources/js/views/dashboard/reference` directory.
     */
    public function getReferenceView(): string|bool|null
    {
        return isset($this->referenceView) ? $this->referenceView : null;
    }

    /**
     * Get Vue record view name.
     *
     * View must be placed in `source/resources/js/views/dashboard/record` directory.
     */
    public function getRecordView(): string|bool|null
    {
        return isset($this->recordView) ? $this->recordView : null;
    }

    /**
     * Get Vue sidebar menu parent item
     */
    public function getSidebarMenuParent(): ?string
    {
        return $this->sidebarMenuParent;
    }

    /**
     * Get reference label
     */
    public function getLabel(): string
    {
        return $this->label ?? trans_choice($this->getLabelKey(), 1);
    }

    /**
     * Get reference plural label
     */
    public function getPluralLabel(): string
    {
        return $this->pluralLabel ?? trans_choice($this->getLabelKey(), 2);
    }

    /**
     * Get reference icon
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Get reference menu item order
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * Setup reference additional filters
     *
     * Example:
     * ```
     * return [
     *      'my_field' => fn (Builder $query, $value, array $filters) => $query->where('my_field_id', '=', $value)
     * ];
     * ```
     */
    public function makeFilters(): array
    {
        return [];
    }

    /**
     * Get reference filters
     */
    public function getFilters(): array
    {
        $filters = $this->makeFilters();
        $relatedFilters = $this->getRelatedFilters();

        return array_merge($filters, $relatedFilters);
    }

    /**
     * Get model field name to display to user, e.g. "name", "display_name", etc
     */
    public function getPrimaryDisplayField(): ?string
    {
        return $this->primaryDisplayField;
    }

    /**
     * Determine the user can access references records
     */
    public function canRead(User $user = null): bool
    {
        return true;
    }

    /**
     * Determine the user can create new record
     */
    public function canCreate(User $user = null): bool
    {
        return true;
    }

    /**
     * Determine the user can update records
     */
    public function canUpdate(User $user = null): bool
    {
        return true;
    }

    /**
     * Determine the user can remove records
     */
    public function canDelete(User $user = null): bool
    {
        return true;
    }

    /**
     * Determine the reference can be used in indicators
     */
    public function canAttachIndicators(): bool
    {
        return $this->indicators;
    }

    /**
     * Determine Vue router reference route meta
     */
    public function getReferenceMeta(): array
    {
        return [];
    }

    /**
     * Determine Vue router record route meta
     */
    public function getRecordMeta(): array
    {
        return [];
    }

    /**
     * Get label translation key
     */
    protected function getLabelKey(): string
    {
        $base = class_basename($this->getModel());
        $key = "reference.{$base}";

        return Lang::has($key) ? $key : $base;
    }

    protected function getRelatedFilters(): array
    {
        $filters = [];
        $fieldsSchema = $this->getSchema();

        foreach ($fieldsSchema as $field => $schema) {
            /** @var ReferenceFieldSchema $schema */

            if ($relation = $schema->getRelation()) {
                $filters[$field] = function (Builder $query, $value) use ($relation) {
                    $query->whereHas($relation, fn (Builder $relatedQuery) => $relatedQuery->whereKey($value));
                };
            }
        }

        return $filters;
    }
}
