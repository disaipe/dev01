<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownContent extends Component
{
    protected string $view = 'forms.components.raw-html-content';

    public string|Htmlable  $content;

    public function __construct(string|Htmlable $content = null)
    {
        $this->setContent($content);
    }

    public static function make(string $content): static
    {
        $static = app(static::class, ['content' => $content]);
        $static->configure();

        return $static;
    }

    public function setContent(string|Htmlable $content = null): static
    {
        if ($content) {
            $parse = new GithubFlavoredMarkdownConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => true
            ]);

            $id = 'md_'.Str::random(6);
            $this->content = "<div id='{$id}'><style type='text/css'>#{$id} * { all: revert; }</style>".$parse->convert($content).'</div>';
        } else {
            $this->content = $content;
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function fromFile($path): static
    {
        $content = File::get($path);
        $this->setContent($content);

        return $this;
    }
}
