<?php

namespace App\Modules\ManageEngineSD;

use App\Core\Module\ModuleManager;
use App\Core\Report\Expression\Expression;
use App\Modules\ManageEngineSD\Models\SDServiceDefinition;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class SDServiceTimeExpression implements Expression
{
    protected array $serviceId;

    public function __construct(...$args)
    {
        $this->serviceId = Arr::get($args, 'service_id') ?? [];
    }

    public function exec(Builder $query): float
    {
        /** @var ModuleManager $modules */
        $modules = app('modules');
        $module = $modules->getByKey('ManageEngineSD');
        $closedStatusIds = $module->getConfig('closes_statuses');

        $wo = $query
            ->whereRelation('charges', 'timespent', '>', 0)
            ->whereHas('status', function (Builder $query) use ($closedStatusIds) {
                $query->whereKey($closedStatusIds);
            })
            ->whereHas('service', function (Builder $query) {
                $query->whereKey($this->serviceId);
            })
            ->withSum('charges', 'timespent')
            ->get();

        return round($wo->sum('hours'), 2);
    }

    public static function label(): string
    {
        return __('mesd::messages.$expression.$timer.label');
    }

    public static function form(): array
    {
        return [
            Select::make('service_id')
                ->label(__('mesd::messages.$expression.$timer.service'))
                ->helperText(__('mesd::messages.$expression.$timer.service help'))
                ->multiple()
                ->options(function () {
                    try {
                        return SDServiceDefinition::query()
                            ->where('status', '=', 'ACTIVE')
                            ->where('isdeleted', '=', false)
                            ->orderBy('name')
                            ->get()
                            ->pluck('name', 'serviceid');
                    } catch (\Exception $e) {
                        Filament::notify('danger', $e->getMessage());
                        return [];
                    }
                })
        ];
    }
}
