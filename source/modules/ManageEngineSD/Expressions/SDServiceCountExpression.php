<?php

namespace App\Modules\ManageEngineSD\Expressions;

use App\Core\Module\ModuleManager;
use App\Core\Report\IQueryExpression;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class SDServiceCountExpression extends SDServiceExpression implements IQueryExpression
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
            ->whereHas('status', function (Builder $query) use ($closedStatusIds) {
                $query->whereKey($closedStatusIds);
            })
            ->whereHas('service', function (Builder $query) {
                $query->whereKey($this->serviceId);
            })
            ->get();

        return $wo->count();
    }

    public static function label(): string
    {
        return __('mesd::messages.$expression.$counter.label');
    }

    public static function form(): array
    {
        return [
            Select::make('service_id')
                ->label(__('mesd::messages.$expression.$counter.service'))
                ->helperText(__('mesd::messages.$expression.$counter.service help'))
                ->multiple()
                ->searchable()
                ->getSearchResultsUsing(fn ($search) => self::getServices($search))
                ->options(fn () => self::getServices())
                ->lazy(),
        ];
    }
}