<?php

namespace App\Filament\Components;

use Closure;
use Filament\Forms\Components\Checkbox;

class FormButton extends Checkbox
{
    protected string $view = 'filament.component.formButton';
    public Closure $callback;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerListeners([
            'run' => [
                function (FormButton $component): void {
                    if ($component->callback) {
                        call_user_func($component->callback);
                    }
                }
            ]
        ]);
    }

    public function action(\Closure $closure): static
    {
        $this->callback = $closure;

        return $this;
    }
}
