<?php

namespace App\Models;

use App\Core\Indicator\IndicatorManager;
use App\Core\Module\ModuleManager;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class StaticIndicator extends Model
{
    use Sushi;

    protected array $schema = [
        'id' => 'integer',
        'code' => 'string',
        'name' => 'string',
        'module' => 'string',
        'published' => 'boolean',
    ];

    public function getRows(): array
    {
        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');

        /** @var ModuleManager $modules */
        $modules = app('modules');

        $rows = collect($indicators->getIndicators())
            ->whereNotNull('module')
            ->map(function (\App\Core\Indicator\Indicator  $indicator) use($modules) {
                return [
                    'code' => $indicator->code,
                    'name' => $indicator->name,
                    'module' => $modules->getByKey($indicator->module)?->getName(),
                    'published' => true,
                ];
            });

        return $rows->values()->toArray();
    }
}
