<?php

namespace App\Core\Report\Expression;

use App\Core\Indicator\IndicatorManager;
use App\Core\Report\Expression;
use App\Core\Report\IExpression;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;

class IndicatorSumExpression extends Expression implements IExpression
{
    public function exec(): float
    {
        return 0;
    }

    public static function label(): string
    {
        return __('admin.$indicator.type.indicators sum expression');
    }

    public static function form(): array
    {
        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicatorOptions = collect($indicators->getIndicators())
            ->pluck('name', 'code')
            ->sort();

        return [
            Repeater::make('schema.indicators')
                ->label(trans_choice('reference.Indicator', 2))
                ->schema([
                    Select::make('code')
                        ->hiddenLabel()
                        ->options($indicatorOptions),
                ]),
        ];
    }

    public static function disabled(array $state): bool
    {
        return false;
    }
}
