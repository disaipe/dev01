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
     *
     * @return ReferenceFieldSchema
     */
    public static function make(): ReferenceFieldSchema
    {
        return new self();
    }

    /**
     * Set field label
     *
     * @param string $label string to display
     * @return $this
     */
    public function label(string $label): static
    {
        Arr::set($this->attributes, 'label', $label);
        return $this;
    }

    /**
     * Make field visible by default
     *
     * @param bool $isVisible
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
     * @param bool $isHidden
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
     * @param array $piniaFieldDefinition
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
        $this->rules []= 'required';
        return $this;
    }

    /**
     * Determine field type is array
     *
     * @return $this
     */
    public function array(): static
    {
        $this->rules []= 'array';
        return $this;
    }

    /**
     * Set field max length/value
     *
     * @param string|int $length
     * @return $this
     */
    public function max(string|int $length): static
    {
        $this->rules []= 'max:' . $length;;
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
     *
     * @return bool
     */
    public function isEagerLoad(): bool
    {
        return $this->eagerLoad;
    }

    /**
     * Get field definition as array
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = $this->attributes;
        $result['rules'] = Arr::join($this->rules, '|');

        return $result;
    }

    /**
     * Serialize field to json array
     *
     * @return array
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