<?php

namespace App\Core\Report\Expression;

use App\Core\Report\IQueryExpression;
use App\Forms\Components\RawHtmlContent;
use Illuminate\Database\Eloquent\Builder;

class CountExpression implements IQueryExpression
{
    protected string $column;

    public function __construct(string $column = '*')
    {
        $this->column = $column;
    }

    public function exec(Builder $query): float
    {
        return $query->count($this->column);
    }

    public static function label(): string
    {
        return __('admin.$expression.count');
    }

    public static function form(): array
    {
        return [
            RawHtmlContent::make(__('admin.$indicator.count helper')),
        ];
    }
}
