<?php

namespace App\Core\Reference;

use App\Core\ReferenceController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class ReferenceEntry
{
    protected string $model;

    protected ?string $prefix;

    protected ?string $name;

    protected ?string $view;

    protected ?string $icon = 'Collection';

    protected bool $piniaBindings = true;

    protected array $schema = [];

    public function getName(): string
    {
        return $this->name ?? class_basename($this->getModel());
    }

    public function controller(): ReferenceController
    {
        return ReferenceController::fromModel($this->getModel(), $this);
    }

    public function getPrefix(): string
    {
        return $this->prefix ?? Str::snake(class_basename($this->getModel()));
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getSchema(): array
    {
        return $this->schema;
    }

    public function hasPiniaBindings(): bool
    {
        return $this->piniaBindings;
    }

    public function getPiniaFields(): ?array
    {
        return Arr::map($this->getSchema(), function ($field) {
            return Arr::get($field, 'pinia');
        });
    }

    public function getView(): ?string
    {
        return $this->view ?? null;
    }

    public function getLabel(): string
    {
        return trans_choice($this->getLabelKey(), 1);
    }

    public function getPluralLabel(): string
    {
        return trans_choice($this->getLabelKey(), 2);
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function canCreate(): bool
    {
        return true;
    }

    public function canUpdate(): bool
    {
        return true;
    }

    public function canDelete(): bool
    {
        return true;
    }

    protected function getLabelKey(): string
    {
        $base = class_basename($this->getModel());
        $key = "reference.{$base}";

        return Lang::has($key) ? $key : $base;
    }
}
