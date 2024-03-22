<?php

namespace App\Core\Report;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method void beforeExec(Builder $query) modify query before it will be executed
 */
interface IQueryExpression extends IExpression
{
    public function exec(Builder $query): float;
}
