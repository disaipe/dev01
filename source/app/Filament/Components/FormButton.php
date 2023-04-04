<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Checkbox;

class FormButton extends Checkbox
{
    protected string $view = 'filament.component.formButton';

    public function onClick(\Closure $closure): static
    {
        $this
            ->reactive()
            ->afterStateUpdated($closure);

        return $this;
    }
}
