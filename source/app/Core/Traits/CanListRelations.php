<?php

namespace App\Core\Traits;

use App\Attributes\Lazy;
use Illuminate\Support\Arr;
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
                'lazy' => collect($method->getAttributes())
                    ->map(fn ($attribute) => $attribute->getName())
                    ->contains(Lazy::class),
            ])
            ->filter(fn (array $item) => ! Arr::get($item, 'lazy'))
            ->all();
    }
}
