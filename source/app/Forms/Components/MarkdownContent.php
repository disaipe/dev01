<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownContent extends Component
{
    protected string $view = 'htmlable';

    public string|Htmlable|Closure $content;

    public function __construct(string|Htmlable|Closure|null $content = null)
    {
        $this->setContent($content);
    }

    public static function make(string $content): static
    {
        $static = app(static::class, ['content' => $content]);
        $static->configure();

        return $static;
    }

    public function setContent(string|Htmlable|Closure|null $content = null): static
    {
        if ($content && (is_string($content) || $content instanceof Htmlable)) {
            $parse = new GithubFlavoredMarkdownConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => true,
            ]);

            $id = 'md_'.Str::random(6);
            $this->content = "<div id='{$id}'><style type='text/css'>#{$id} * { all: revert; }</style>".$parse->convert($content).'</div>';
        } else {
            $this->content = $content;
        }

        return $this;
    }

    public function fromFile($path): static
    {
        $content = File::get($path);
        $this->setContent($content);

        return $this;
    }

    public function getContent(): mixed
    {
        return $this->evaluate($this->content);
    }
}
