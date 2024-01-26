<?php

namespace App\Core\Reference;

use App\Core\Reference\PiniaStore\PiniaAttribute;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use JsonSerializable;

class ReferenceFieldSchema implements Arrayable, Jsonable, JsonSerializable
{
    protected array $attributes;

    protected array $rules;

    protected bool $eagerLoad = false;

    protected function __construct()
    {
        $this->attributes = [];
        $this->rules = [];
    }

    /**
     * Make reference field schema instance
     */
    public static function make(): ReferenceFieldSchema
    {
        return new self();
    }

    /**
     * Set field label
     *
     * @param  string  $label string to display
     * @return $this
     */
    public function label(string $label): static
    {
        Arr::set($this->attributes, 'label', $label);

        return $this;
    }

    /**
     * Returns field label
     */
    public function getLabel(): ?string
    {
        return Arr::get($this->attributes, 'label');
    }

    /**
     * Set field description
     *
     * @param  string  $description helper text to display near the field
     * @return $this
     */
    public function description(string $description): static
    {
        Arr::set($this->attributes, 'description', $description);

        return $this;
    }

    /**
     * Make field visible by default
     *
     * @return $this
     */
    public function visible(bool $isVisible = true): static
    {
        Arr::set($this->attributes, 'visible', $isVisible);

        return $this;
    }

    /**
     * Make field hidden in tables and forms
     *
     * @return $this
     */
    public function hidden(bool $isHidden = true): static
    {
        Arr::set($this->attributes, 'hidden', $isHidden);

        return $this;
    }

    /**
     * Set field`s pinia definition
     *
     * @return $this
     */
    public function pinia(array $piniaFieldDefinition): static
    {
        Arr::set($this->attributes, 'pinia', $piniaFieldDefinition);

        return $this;
    }

    /**
     * Make field is required
     *
     * @return $this
     */
    public function required(): static
    {
        $this->rules[] = 'required';

        return $this;
    }

    /**
     * Make field readonly
     *
     * @return $this
     */
    public function readonly(): static
    {
        Arr::set($this->attributes, 'readonly', true);

        return $this;
    }

    /**
     * Set field type textarea
     *
     * @return $this
     */
    public function textarea(): static
    {
        Arr::set($this->attributes, 'type', 'textarea');

        return $this;
    }

    /**
     * Set field type checkbox
     *
     * @return $this
     */
    public function checkbox(): static
    {
        Arr::set($this->attributes, 'type', 'checkbox');

        return $this;
    }

    /**
     * Set filed type password
     */
    public function password(): static
    {
        Arr::set($this->attributes, 'type', 'password');

        return $this;
    }

    /**
     * Set field display filter
     *
     * @param  string  $name filter name
     * @param  array|null  $args filter additional arguments
     * @return $this
     */
    public function displayFilter(string $name, array $args = null): static
    {
        Arr::set($this->attributes, 'filter', [$name, $args]);

        return $this;
    }

    /**
     * Set field type select with options
     *
     * @return $this
     */
    public function options(array $values): static
    {
        Arr::set($this->attributes, 'type', 'select');
        Arr::set($this->attributes, 'options', $values);

        return $this;
    }

    /**
     * Determine field type is array
     *
     * @return $this
     */
    public function array(): static
    {
        $this->rules[] = 'array';

        return $this;
    }

    /**
     * Set field max length/value
     *
     * @return $this
     */
    public function max(string|int $length): static
    {
        $this->rules[] = 'max:'.$length;

        return $this;
    }

    /**
     * Determine field as ID
     *
     * @return $this
     */
    public function id(): static
    {
        return $this->hidden()->pinia(PiniaAttribute::uid());
    }

    /**
     * Set field eager loading
     *
     * @return $this
     */
    public function eagerLoad(): static
    {
        $this->eagerLoad = true;

        return $this;
    }

    /**
     * Get field is eager loading
     */
    public function isEagerLoad(): bool
    {
        return $this->eagerLoad;
    }

    /**
     * Mark relation as lazy - related reference will
     * not be automatically loaded
     */
    public function lazy(): static
    {
        Arr::set($this->attributes, 'lazy', true);

        return $this;
    }

    /**
     * Get attribute value by name
     */
    public function getAttribute(string $attribute): mixed
    {
        return Arr::get($this->attributes, $attribute);
    }

    /**
     * Get field definition as array
     */
    public function toArray(): array
    {
        $result = $this->attributes;
        $result['rules'] = Arr::join($this->rules, '|');

        return $result;
    }

    /**
     * Serialize field to json array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson($options = 0): false|string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
