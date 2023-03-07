<?php

namespace App\Core\Report\Expression;

use Illuminate\Database\Eloquent\Builder;

class CountExpression implements Expression
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
}
