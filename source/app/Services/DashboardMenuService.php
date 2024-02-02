<?php

namespace App\Services;

use App\Support\DashboardMenuItem;
use Illuminate\Support\Collection;

class DashboardMenuService
{
    protected Collection $menu;

    public function __construct()
    {
        $this->menu = collect([]);

        $this->addDefaults();
    }

    public function getMenu(): Collection
    {
        return $this->menu;
    }

    public function addMenuItem(DashboardMenuItem $item): self
    {
        $this->menu->add($item);

        return $this;
    }

    protected function addDefaults(): void
    {
        $this->menu->add(DashboardMenuItem::make('dashboard')
            ->label('Главная')
            ->icon('fluent-mdl2:home')
            ->route(['name' => 'dashboard'])
            ->order(1)
        );

        $this->menu->add(DashboardMenuItem::make('references')
            ->label('Справочники')
            ->icon('fluent-mdl2:product-catalog'));
    }
}
