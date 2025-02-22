<?php

namespace App\Core\Report\Traits;

use App\Core\Indicator\Indicator;
use App\Core\Reference\ReferenceFieldSchema;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait ReportExtend
{
    use ReportGlobalSettings, ReportServiceSettings;

    /**
     * Make description for the service
     *
     * @param int|string $serviceId
     * @return array
     */
    public function debugService(int|string $serviceId): array
    {
        $service = $this->services->get($serviceId);

        /** @var Indicator $indicator */
        $indicator = $this->getServiceIndicators($service)[$serviceId];

        return $this->debugIndicator($indicator);
    }

    /**
     * Make description for the indicator
     *
     * @param Indicator|string $indicator
     * @return array|null
     */
    public function debugIndicator(Indicator|string $indicator): ?array
    {
        $indicatorInstance = null;

        if (is_string($indicator)) {
            $indicatorInstance = $this->indicatorManager->getByCode($indicator);
        } elseif (get_class($indicator) === Indicator::class) {
            $indicatorInstance = $indicator;
        }

        if (!$indicatorInstance) {
            return null;
        }

        return $indicatorInstance
            ->setContext($this->getContext())
            ->debug();
    }

    protected function makeDebugRowsForService(array $item): array
    {
        /** @var Service $service */
        $service = Arr::get($item, 'service');

        if (! $this->isServiceExcluded($service)) {
            return [$service->getKey() => null];
        }

        /** @var ?EloquentCollection $data */
        $data = Arr::get($item, 'debug.data');
        $columns = Arr::get($item, 'debug.columns', []);

        /** @var ?string $refName */
        $refName = Arr::get($item, 'debug.reference');

        /** @var ?Indicator $indicator */
        $indicator = Arr::get($item, 'indicator');

        $mutators = [];

        // hide reference columns what is not visible (global)
        if ($refName) {
            $reference = $this->referenceManager->getByName($refName);

            if (! count($columns) && $reference) {
                $schema = $reference->getSchema();

                $columns = Arr::where(
                    $schema,
                    fn(ReferenceFieldSchema $field, string $name) =>
                        ! $this->isReferenceFieldExcludedGlobally($refName, $name, $field)
                        && $this->isServiceReferenceFieldVisible($service, $name)
                );

                if (! count($columns)) {
                    $columns = $schema;
                }

                $columns = Arr::map($columns, fn(ReferenceFieldSchema $col) => $col->getLabel());
            }

            $relations = $reference?->getModelInstance()->listRelations() ?? [];
            $includedRelations = array_keys(array_intersect_key($relations, $columns));

            // load required relations
            $data->load($includedRelations);

            // map model records to only needed keys
            $data = $data->map(fn(Model $row) => $row->toArray());

            // mutate related records to display them as string
            foreach ($includedRelations as $relationName) {
                $relationModelClass = $reference->getModelInstance()->$relationName()->getModel();
                $relationRef = $this->referenceManager->getByName(class_basename($relationModelClass));
                $relationDisplayField = $relationRef?->getPrimaryDisplayField() ?? 'name';

                if ($relationDisplayField) {
                    $mutators[] = fn(array &$row) => $row[$relationName] = @$row[$relationName][$relationDisplayField];
                }
            }
        }

        // append expression column mutator
        if ($indicator->mutator) {
            $columnOption = $indicator->expression->getOptions('column');

            if ($columnOption) {
                $mutators[] = function (array &$row) use ($indicator, $columnOption) {
                    if (isset($row[$columnOption])) {
                        $row[$columnOption] = $indicator->mutateValue($row[$columnOption]);
                    }
                };
            }
        }

        // apply mutators
        if (count($mutators)) {
            $data = $data->map(function (array $row) use ($mutators) {
                foreach ($mutators as $mutator) {
                    $mutator($row);
                }

                return $row;
            });
        }

        return [$service->getKey() => [
            'service' => [
                'id' => Arr::get($item, 'service.id'),
                'name' => Arr::get($item, 'page_name') ?? Arr::get($item, 'service.name'),
            ],
            'columns' => array_values($columns),
            'rows' => $data->select(array_keys($columns))->map(fn($row) => array_values($row))->toArray(),
        ]];
    }
}