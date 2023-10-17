<?php

namespace App\Core\Indicator;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class IndicatorManager
{
    private array $indicators = [];

    public function __construct()
    {
        $this->registerStoredIndicators();
    }

    /**
     * @param  Indicator|Indicator[]  $indicators
     */
    public function register(Indicator|array $indicators): void
    {
        foreach (Arr::wrap($indicators) as $indicator) {
            $this->indicators[$indicator->code] = $indicator;
        }
    }

    public function getIndicators(): array
    {
        return $this->indicators;
    }

    public function getByCode(string $code): ?Indicator
    {
        return Arr::get($this->indicators, $code);
    }

    public function registerStoredIndicators(): void
    {
        if (! Schema::hasTable('indicators')) {
            return;
        }

        /** @var \App\Models\Indicator[] $indicators */
        $indicators = \App\Models\Indicator::query()
            ->enabled()
            ->get();

        foreach ($indicators as $indicator) {
            $this->register(Indicator::fromArray([
                'code' => $indicator->code,
                'type' => $indicator->type,
                'name' => $indicator->name,
                'schema' => $indicator->schema,
                'module' => Arr::get($indicator->schema, 'module'),
            ]));
        }
    }
}
