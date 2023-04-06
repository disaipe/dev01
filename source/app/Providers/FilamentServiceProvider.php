<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Columns\CheckboxColumn;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Filament::serving(function () {
            $this->registerNavigation();
            $this->registerMacros();
        });
    }

    protected function registerNavigation()
    {
        Filament::registerNavigationItems([
            NavigationItem::make(__('admin.menu.portal'))
                ->url('/')
                ->icon('heroicon-s-globe-alt')
                ->sort(0),
        ]);

        Filament::registerNavigationGroups([
            NavigationGroup::make(__('admin.menu.links'))
                ->icon('heroicon-s-link'),

            NavigationGroup::make(__('admin.menu.access'))
                ->icon('heroicon-s-lock-closed'),

            NavigationGroup::make(__('admin.menu.debug'))
                ->icon('heroicon-s-code')
                ->collapsed(),
        ]);
    }

    protected function registerMacros()
    {
        CheckboxColumn::macro('toggle', function () {
            /** @var CheckboxColumn $this */
            $this->action(function ($record, $column) {
                $name = $column->getName();
                $record->update([
                    $name => ! $record->$name,
                ]);
            });

            return $this;
        });
    }
}
