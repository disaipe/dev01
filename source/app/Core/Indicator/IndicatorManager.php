<?php

namespace App\Core\Indicator;

use Illuminate\Support\Arr;

class IndicatorManager
{
    private array $indicators = [];

    /**
     * @param Indicator|Indicator[] $indicators
     * @return void
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
}
