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

    public function registerStoredIndicators(): void
    {
        if (! Schema::hasTable('indicators')) {
            return;
        }

        $indicators = \App\Models\Indicator::query()
            ->enabled()
            ->get();

        foreach ($indicators as $indicator) {
            $this->register(Indicator::fromModel($indicator));
        }
    }
}
