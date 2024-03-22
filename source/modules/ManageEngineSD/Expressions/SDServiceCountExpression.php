<?php

namespace App\Modules\ManageEngineSD\Expressions;

use App\Core\Report\IQueryExpression;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class SDServiceCountExpression extends SDServiceExpression implements IQueryExpression
{
    public function exec(Builder $query): float
    {
        return $query->count();
    }

    public static function label(): string
    {
        return __('mesd::messages.$expression.$counter.label');
    }

    public static function form(): array
    {
        return [
            Select::make('service_id')
                ->label(__('mesd::messages.$expression.$counter.service'))
                ->helperText(__('mesd::messages.$expression.$counter.service help'))
                ->multiple()
                ->searchable()
                ->getSearchResultsUsing(fn ($search) => self::getServices($search))
                ->options(fn () => self::getServices())
                ->lazy(),
        ];
    }
}
