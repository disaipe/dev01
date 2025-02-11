<?php

namespace App\Modules\ManageEngineSD\Expressions;

use App\Core\Report\IQueryExpression;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class SDServiceTimeExpression extends SDServiceExpression implements IQueryExpression
{
    public function exec(Builder $query): float
    {
        $timespent = $query->sum('timespent');

        return round($timespent / 1000 / 60 / 60, 2);
    }

    public static function label(): string
    {
        return __('mesd::messages.$expression.$timer.label');
    }

    public static function form(): array
    {
        return [
            Select::make('service_id')
                ->label(__('mesd::messages.$expression.$timer.service'))
                ->helperText(__('mesd::messages.$expression.$timer.service help'))
                ->multiple()
                ->searchable()
                ->getSearchResultsUsing(fn ($search) => self::getServices($search))
                ->options(fn () => self::getServices())
                ->lazy(),
        ];
    }
}
