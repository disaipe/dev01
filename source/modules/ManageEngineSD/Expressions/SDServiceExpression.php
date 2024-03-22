<?php

namespace App\Modules\ManageEngineSD\Expressions;

use App\Core\Module\ModuleManager;
use App\Core\Report\Expression;
use App\Modules\ManageEngineSD\Models\SDServiceDefinition;
use App\Modules\ManageEngineSD\Models\SDWorkorder;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Kirschbaum\PowerJoins\PowerJoinClause;

class SDServiceExpression extends Expression
{
    public static function getServices(string $search = null): array
    {
        try {
            return SDServiceDefinition::query()
                ->where('status', '=', 'ACTIVE')
                ->where('isdeleted', '=', false)
                ->when($search, fn (Builder $query) => $query->whereRaw('name like ?', '%'.$search.'%'))
                ->orderBy('name')
                ->get()
                ->pluck('name', 'serviceid')
                ->toArray();
        } catch (\Exception $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }

        return [];
    }

    public static function disabled(array $state): bool
    {
        $reference = Arr::get($state, 'reference');

        return $reference !== class_basename(SDWorkorder::class);
    }

    public function beforeExec(Builder $query): void
    {
        /** @var ModuleManager $modules */
        $modules = app('modules');
        $module = $modules->getByKey('ManageEngineSD');
        $closedStatusIds = $module->getConfig('closes_statuses');

        $serviceId = Arr::get($this->options, 'service_id');

        $query
            ->joinRelationship('status', fn (PowerJoinClause $join) => $join->whereIn('workorderstates.statusid', $closedStatusIds))
            ->joinRelationship('service', fn (PowerJoinClause $join) => $join->whereIn('servicedefinition.serviceid', $serviceId));
    }
}
