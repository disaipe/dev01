<?php

namespace App\Core\Report;

interface IExpression
{
    public static function label(): string;

    public static function form(): array;

    public static function disabled(array $state): bool;
}
