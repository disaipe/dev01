<?php

namespace App\Filament\Resources\IndicatorResource\Pages;

use App\Filament\Resources\IndicatorResource;
use App\Models\IndicatorGroup;
use App\Models\StaticIndicator;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListIndicators extends ListRecords
{
    protected static string $resource = IndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $groups = IndicatorGroup::query()->whereHas('indicators')->get();

        $tabs = [
            'all' => Tab::make(__('admin.all')),
            'module' => Tab::make(trans_choice('admin.$indicator.module indicator', 2))
                ->query(fn () => StaticIndicator::query()),
        ];

        foreach ($groups as $group) {
            $tab = Tab::make($group->name)->modifyQueryUsing(
                fn (Builder $query) => $query->where('indicator_group_id', '=', $group->getKey())
            );

            $tabs[$group->getKey()] = $tab;
        }

        return $tabs;
    }
}
