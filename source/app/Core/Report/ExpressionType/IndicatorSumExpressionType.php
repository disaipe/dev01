<?php

namespace App\Core\Report\ExpressionType;

use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Report\Expression\IndicatorSumExpression;
use App\Core\Report\IExpression;
use App\Core\Report\IExpressionType;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class IndicatorSumExpressionType implements IExpressionType
{
    protected array $indicators;

    public function __construct(array $options = [])
    {
        $this->indicators = Arr::get($options, 'schema.indicators') ?? [];
    }

    public static function form(): array
    {
        return IndicatorSumExpression::form();
    }

    public static function label(): string
    {
        return IndicatorSumExpression::label();
    }

    public function calculate(Indicator $indicator)
    {
        $indicatorsData = $this->getIndicatorsValues($indicator);

        return $indicatorsData->sum('value');
    }

    public function debug(Indicator $indicator): array
    {
        return [
            'columns' => [
                ['field' => 'code', 'label' => __('admin.code')],
                ['field' => 'name', 'label' => __('admin.name')],
                ['field' => 'value', 'label' => __('admin.value')],
            ],
            'data' => $this->getIndicatorsValues($indicator),
        ];
    }

    public function getExpression(Indicator $indicator): ?IExpression
    {
        return new IndicatorSumExpression();
    }

    protected function getIndicatorsValues(Indicator $indicator): Collection
    {
        $result = collect([]);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');

        foreach ($this->indicators as $indicatorScheme) {
            $code = Arr::get($indicatorScheme, 'code');
            $expressionIndicator = $indicators->getByCode($code);

            $value = $expressionIndicator
                ->setContext($indicator->context())
                ->exec();

            $result->add(array_merge($expressionIndicator->toArray(), ['value' => $value]));
        }

        return $result;
    }
}
