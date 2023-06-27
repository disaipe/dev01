<?php

namespace App\Core\Report\Expression;

use Illuminate\Support\Arr;

class ExpressionManager
{
    private array $expressions = [];

    public function __construct()
    {
        $this->register(CountExpression::class);
        $this->register(SumExpression::class);
    }

    public function register(string|array $expressions): void
    {
        foreach (Arr::wrap($expressions) as $expression) {
            $this->expressions[class_basename($expression)] = $expression;
        }
    }

    public function getExpressions(): array
    {
        return $this->expressions;
    }

    public function getByKey($key): Expression|string|null
    {
        return Arr::get($this->expressions, $key);
    }
}
