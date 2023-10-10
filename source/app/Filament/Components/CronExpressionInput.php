<?php

namespace App\Filament\Components;

use Closure;
use Cron\CronExpression;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Throwable;

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

        $this->rules([
            function () {
                return function (string $attribute, $value, Closure $fail) {
                    if ($value) {
                        try {
                            new CronExpression($value);
                        } catch (Throwable $e) {
                            $fail($e->getMessage());
                        }
                    }
                };
            },
        ]);
    }
}
