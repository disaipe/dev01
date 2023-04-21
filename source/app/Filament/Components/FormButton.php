<?php

namespace App\Filament\Components;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Concerns\HasName;

class FormButton extends Component
{
    use HasName;

    protected string $view = 'filament.component.formButton';

    protected string $innerEventName = 'formButton::run';

    protected Closure|string $action;

    public function __construct(string $name)
    {
        $this->name($name);
    }

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function action(Closure|string $action): static
    {
        $this->action = $action;

        if (! is_string($this->action)) {
            $this->registerListeners([
                $this->innerEventName => [
                    function (FormButton $component, string $name) {
                        if ($component->getName() === $name) {
                            $component->execute();
                        }
                    },
                ],
            ]);
        }

        return $this;
    }

    public function isPageEvent(): bool
    {
        return is_string($this->action);
    }

    public function getEventName(): string
    {
        return $this->isPageEvent() ? $this->action : $this->innerEventName;
    }

    public function execute()
    {
        if (isset($this->action)) {
            call_user_func($this->action);
        }
    }
}
