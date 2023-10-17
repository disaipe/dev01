<?php

namespace App\Core\Report;

use Illuminate\Database\Eloquent\Builder;

interface IQueryExpression extends IExpression
{
    public function exec(Builder $query): float;
}
