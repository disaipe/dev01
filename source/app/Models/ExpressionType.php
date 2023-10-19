<?php

namespace App\Models;

use App\Core\Report\IExpressionType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;

class ExpressionType
{
    /**
     * @throws BindingResolutionException
     */
    public static function from(string $type, array $options = []): IExpressionType
    {
        $fullClass = Str::start($type, 'App\Core\Report\ExpressionType\\');

        return app()->make($fullClass, ['options' => $options]);
    }
}
