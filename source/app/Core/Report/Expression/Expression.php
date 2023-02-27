<?php

namespace App\Core\Report\Expression;

use Illuminate\Database\Eloquent\Builder;

interface Expression
{
    public function exec(Builder $query): float;
}
