<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

class Alert extends Component
{
    protected string $view = 'filament.component.alert';

    public string|Htmlable|Closure $content;

    public string $type;

    public function __construct(string|Htmlable|Closure $content)
    {
        $this->type = 'info';
        $this->content = $content;
    }

    public static function make(string|Htmlable|Closure $content): static
    {
        $static = app(static::class, ['content' => $content]);
        $static->configure();

        return $static;
    }

    public function info(): static
    {
        $this->type = null;

        return $this;
    }

    public function warn(): static
    {
        $this->type = 'warn';

        return $this;
    }

    public function getContent(): mixed
    {
        return $this->evaluate($this->content);
    }

    public function getType()
    {
        return $this->type;
    }
}
