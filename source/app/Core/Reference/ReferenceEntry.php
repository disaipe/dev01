<?php

namespace App\Core\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use App\Models\CustomReference;
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
     * Reference icon
     */
    protected ?string $icon = 'fluent-mdl2:product-catalog';

    /**
     * Menu order
     */
    protected int $order = 0;

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

    /**
     * Create reference entry from custom reference
     */
    public static function fromCustomReference(CustomReference $customReference): self
    {
        $entry = new self();
        $entry->name = Str::ascii(Str::studly($customReference->name));
        $entry->model = $customReference->getReferenceModel();
        $entry->label = $customReference->label;
        $entry->pluralLabel = $customReference->plural_label;
        $entry->schema = [];

        $fields = Arr::get($customReference->schema, 'fields', []);

        if ($customReference->company_context) {
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

        foreach ($fields as $field) {
            $pk = Arr::get($field, 'pk');
            $name = Arr::get($field, 'name');
            $label = Arr::get($field, 'display_name');
            $type = Arr::get($field, 'type');
            $required = Arr::get($field,  'required');
            $readonly = Arr::get($field, 'readonly');

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
        return ReferenceController::fromModel($this->getModel(), $this);
    }

    /**
     * Get route path prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix ?? Str::snake($this->getName());
    }

    /**
     * Get reference model
     */
    public function getModel(): ReferenceModel|string
    {
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
     * Determine the user can create new record
     */
    public function canCreate(): bool
    {
        return true;
    }

    /**
     * Determine the user can update records
     */
    public function canUpdate(): bool
    {
        return true;
    }

    /**
     * Determine the user can remove records
     */
    public function canDelete(): bool
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
}
