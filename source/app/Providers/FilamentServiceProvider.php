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
            NavigationItem::make('Main')
                ->url('/')
                ->icon('heroicon-o-presentation-chart-line')
                ->group('Links')
                ->sort(1),
        ]);

        Filament::registerNavigationGroups([
            NavigationGroup::make('access')
                ->label(__('admin.menu.access'))
                ->icon('heroicon-s-lock-closed'),
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
