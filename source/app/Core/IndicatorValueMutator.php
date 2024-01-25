<?php

namespace App\Core;

use App\Core\Enums\ReportContextConstant;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class IndicatorValueMutator
{
    protected Collection $mutators;

    public function __construct()
    {
        $this->mutators = collect();

        $this->mutators->put('Fixed', function (float $v, array $mutator, array $context) {
            $values = collect(Arr::get($mutator, 'values', []))->pluck('value', 'company');

            $companyId = Arr::get($context, ReportContextConstant::COMPANY_ID->name);

            if ($values->has($companyId)) {
                return $values->get($companyId);
            }

            return $values->get(null) ?? 0;
        });

        $this->mutators->put('Expression', function (float $v, array $mutator) {
            $expression = Arr::get($mutator, 'value');

            $optimizedExpression = preg_replace('/(?:^|\s)(value)(?:\s|$)/i', 'value', $expression);

            $expressionLanguage = new ExpressionLanguage();

            return $expressionLanguage->evaluate($optimizedExpression, ['value' => $v]);
        });

        $base = 1000;

        $this->mutators->put('ByteToKB', fn (float $v) => $v / $base);
        $this->mutators->put('ByteToMB', fn (float $v) => $v / pow($base, 2));
        $this->mutators->put('ByteToGB', fn (float $v) => $v / pow($base, 3));
        $this->mutators->put('ByteToTB', fn (float $v) => $v / pow($base, 4));

        $this->mutators->put('KBToMB', fn (float $v) => $v / $base);
        $this->mutators->put('KBToGB', fn (float $v) => $v / pow($base, 2));
        $this->mutators->put('KBToTB', fn (float $v) => $v / pow($base, 3));

        $this->mutators->put('MBToGB', fn (float $v) => $v / $base);
        $this->mutators->put('MBToTB', fn (float $v) => $v / pow($base, 2));

        $this->mutators->put('GBToTB', fn (float $v) => $v / $base);
    }

    public function get(): Collection
    {
        return $this->mutators;
    }

    public function getByName(string $name): ?Closure
    {
        return $this->mutators->get($name);
    }
}
