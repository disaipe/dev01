<?php

namespace App\Core\Report\Traits;

use App\Core\Reference\ReferenceFieldSchema;
use App\Facades\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait ReportGlobalSettings
{
    protected array $commonExcludedFields = [];

    protected array $excludedFieldsByReference = [];

    protected array $config;

    protected function prepareDetailedReportExcludedFields(): void
    {
        $this->config = Config::get('report');
        $this->commonExcludedFields = Arr::get($this->config, 'fields.exclude') ?? [];

        $excludedFieldsInReferences = Arr::get($this->config, 'fields.references.exclude') ?? [];
        $this->excludedFieldsByReference = Arr::pluck($excludedFieldsInReferences, 'fields', 'reference');
    }

    protected function isReferenceFieldExcludedGlobally(string $reference, string $name, ReferenceFieldSchema $field): bool
    {
        if ($field->isHidden()) {
            return true;
        }

        $excluded = array_merge(
            $this->commonExcludedFields,
            Arr::get($this->excludedFieldsByReference, $reference) ?? []
        );

        if (in_array($name, $excluded) || in_array($field->getLabel(), $excluded)) {
            return true;
        }

        return false;
    }

    protected function mergeServices(Collection $values): Collection
    {
        // Process merging
        if (Arr::get($this->config, 'merging.enabled') !== true) {
            return $values;
        }

        $mergingGroups = Arr::get($this->config, 'merging.groups') ?? [];

        foreach ($mergingGroups as $group) {
            $values = $values->map(function (array $item, string $key) use ($group) {
                $services = Arr::get($group, 'service');

                if (is_array($services) && in_array($key, $services)) {
                    if ($key === $services[0]) {
                        $item['page_name'] = $group['merged_name'];
                        return $item;
                    } else {
                        return null;
                    }
                }

                return $item;
            });
        }

        return $values;
    }
}