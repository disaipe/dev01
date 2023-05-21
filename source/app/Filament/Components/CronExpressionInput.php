<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;

class CronExpressionInput extends Field
{
    use HasPlaceholder;

    protected string $view = 'filament.component.cron-expression-input';

    public function setUp(): void
    {
        $this->viewData([
            'locale' => config('app.locale'),
        ]);

        if (! $this->placeholder) {
            $this->placeholder = '- - - - -';
        }
    }
}
