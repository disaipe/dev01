<?php

namespace App\Core\Traits;

use ReflectionClass;
use ReflectionMethod;

trait CanListRelations
{
    public function listRelations(): array
    {
        $reflector = new ReflectionClass(get_called_class());

        return collect($reflector->getMethods())
            ->filter(
                fn (ReflectionMethod $method) => ! empty($method->getReturnType()) &&
                    str_contains(
                        $method->getReturnType(),
                        'Illuminate\Database\Eloquent\Relations'
                    )
            )
            ->map(fn (ReflectionMethod $method) => [
                'name' => $method->getName(),
                'type' => $method->getReturnType(),
            ])
            ->pluck('type', 'name')
            ->all();
    }
}
