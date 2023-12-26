<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

if (! function_exists('resolveModel')) {
    function resolveModel(string $model): Model|null
    {
        $className = collect(get_declared_classes())
            ->filter(fn ($class) => Str::endsWith($class, "\\$model"))
            ->filter(fn ($class) => is_subclass_of($class, Model::class))
            ->first();

        if (! $className) {
            return null;
        }

        /** @var Model $modelInstance */
        $modelInstance = app()->make($className);

        return $modelInstance;
    }
}
