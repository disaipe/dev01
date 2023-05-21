<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

class RawHtmlContent extends Component
{
    protected string $view = 'htmlable';

    public string|Htmlable|Closure  $content;

    public function __construct(string|Htmlable|Closure $content)
    {
        $this->content = $content;
    }

    public static function make(string|Htmlable|Closure $content): static
    {
        $static = app(static::class, ['content' => $content]);
        $static->configure();

        return $static;
    }

    public function getContent(): mixed
    {
        return $this->evaluate($this->content);
    }
}
