<?php

namespace App\Core\Report;

use App\Core\Indicator\Indicator;

interface IExpressionType
{
    public static function form(): array;

    public static function label(): string;

    public function getExpression(Indicator $indicator): ?IExpression;

    public function debug(Indicator $indicator): array;
}
