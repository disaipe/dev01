<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

class RawHtmlContent extends Component
{
    protected string $view = 'forms.components.raw-html-content';

    public string|Htmlable  $content;

    public function __construct(string|Htmlable $content)
    {
        $this->content = $content;
    }

    public static function make(string $content): static
    {
        $static = app(static::class, ['content' => $content]);
        $static->configure();

        return $static;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
