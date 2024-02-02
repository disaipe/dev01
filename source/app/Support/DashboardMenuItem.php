<?php

namespace App\Support;

class DashboardMenuItem
{
    public string $name;

    public ?string $label;

    public ?string $icon;

    public ?array $route;

    public int $order = 99;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): static
    {
        return new self($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function route(array $route): static
    {
        $this->route = $route;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function order(int $order): static
    {
        $this->order = $order;

        return $this;
    }
}
