<?php

namespace App\Core\Reference;

use App\Core\ReferenceController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class ReferenceEntry
{
    /**
     * Reference linked model
     *
     * @var string
     */
    protected string $model;

    /**
     * Route prefix for the controller
     *
     * @var string|null
     */
    protected ?string $prefix;

    /**
     * Reference name
     *
     * @var string|null
     */
    protected ?string $name;

    /**
     * Reference view name for the front-end application
     *
     * @var string|null
     */
    protected ?string $view;

    /**
     * Reference icon
     *
     * @var string|null
     */
    protected ?string $icon = 'Collection';

    /**
     * Menu order
     *
     * @var int
     */
    protected int $order = 0;

    /**
     * Determine the schema contains bindings for the Pinia-orm model
     *
     * @var bool
     */
    protected bool $piniaBindings = true;

    /**
     * Model fields schema
     *
     * @var array
     */
    protected array $schema = [];

    /**
     * Get reference name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? class_basename($this->getModel());
    }

    /**
     * Get reference controller
     *
     * @return ReferenceController
     */
    public function controller(): ReferenceController
    {
        return ReferenceController::fromModel($this->getModel(), $this);
    }

    /**
     * Get route path prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix ?? Str::snake(class_basename($this->getModel()));
    }

    /**
     * Get reference model
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Get model fields schema
     *
     * Used to operate with model fields on front-end
     *
     * @return array
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    /**
     * Returns the model has pinia bindings in field schema
     *
     * @return bool
     */
    public function hasPiniaBindings(): bool
    {
        return $this->piniaBindings;
    }

    /**
     * Get fields definition for the Pinia-orm model
     *
     * @return array|null
     */
    public function getPiniaFields(): ?array
    {
        return Arr::map($this->getSchema(), function ($field) {
            return Arr::get($field, 'pinia');
        });
    }

    /**
     * Get Vue reference view name.
     *
     * View must be placed in `source/resources/js/views/dashboard/reference` directory.
     *
     * @return string|null
     */
    public function getView(): ?string
    {
        return $this->view ?? null;
    }

    /**
     * Get reference label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return trans_choice($this->getLabelKey(), 1);
    }

    /**
     * Get reference plural label
     *
     * @return string
     */
    public function getPluralLabel(): string
    {
        return trans_choice($this->getLabelKey(), 2);
    }

    /**
     * Get reference icon
     *
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Get reference menu item order
     *
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * Determine the user can create new record
     *
     * @return bool
     */
    public function canCreate(): bool
    {
        return true;
    }

    /**
     * Determine the user can update records
     *
     * @return bool
     */
    public function canUpdate(): bool
    {
        return true;
    }

    /**
     * Determine the use can remove records
     *
     * @return bool
     */
    public function canDelete(): bool
    {
        return true;
    }

    /**
     * Get label translation key
     *
     * @return string
     */
    protected function getLabelKey(): string
    {
        $base = class_basename($this->getModel());
        $key = "reference.{$base}";

        return Lang::has($key) ? $key : $base;
    }
}
