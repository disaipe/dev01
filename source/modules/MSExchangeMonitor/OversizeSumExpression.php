<?php

namespace App\Modules\MSExchangeMonitor;

use App\Core\Module\ModuleManager;
use App\Core\Report\IExpression;
use App\Utils\Size;
use Illuminate\Database\Eloquent\Builder;

class OversizeSumExpression implements IExpression
{
    public function exec(Builder $query): float
    {
        /** @var ModuleManager $modules */
        $modules = app('modules');
        $module = $modules->getByKey('msExchangeMonitor');

        $limitGb = $module->getConfig('default_limit');
        $overSizeStep = $module->getConfig('oversize_step');

        $limitF = floatval($limitGb ?? 1);
        $overSizeStep = floatval($overSizeStep ?? 1);

        $items = $query
            ->selectRaw(
                'ceil((cast(total_item_size as signed) - ?) / ?) as oversizeGb',
                [Size::Gigabyte($limitF), Size::Gigabyte($overSizeStep)]
            )
            ->where('total_item_size', '>', Size::Gigabyte($limitF))
            ->get();

        return $items->sum(fn ($model) => ($model['oversizeGb']));
    }

    public static function label(): string
    {
        // internal indicator, no label required
        return '';
    }

    public static function form(): array
    {
        // internal indicator, no label required
        return [];
    }
}
