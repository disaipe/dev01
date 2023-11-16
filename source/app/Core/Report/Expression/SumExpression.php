<?php

namespace App\Core\Report\Expression;

use App\Core\Report\Expression;
use App\Core\Report\IQueryExpression;
use App\Forms\Components\RawHtmlContent;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class SumExpression extends Expression implements IQueryExpression
{
    protected ?string $column;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->column = Arr::get($options, 'column', '*');
    }

    public function exec(Builder $query): float
    {
        return $query->sum($this->column);
    }

    public static function label(): string
    {
        return __('admin.$expression.sum');
    }

    public static function form(): array
    {
        return [
            RawHtmlContent::make(__('admin.$indicator.sum helper')),
            TextInput::make('column')
                ->label(__('admin.$indicator.column'))
                ->required(),
        ];
    }

    public static function disabled(array $state): bool
    {
        return false;
    }
}
