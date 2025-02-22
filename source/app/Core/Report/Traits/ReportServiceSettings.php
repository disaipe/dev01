<?php

namespace App\Core\Report\Traits;

use App\Core\Enums\Visibility;
use App\Models\Service;
use Illuminate\Support\Arr;

trait ReportServiceSettings
{
    /**
     * The service is excluded from extended report or not
     *
     * @
     */
    protected function isServiceExcluded(Service $service)
    {
        return Arr::get($service->options ?? [], 'report.enabled', true);
    }

    /**
     * The service field is excluded from extended report or not
     */
    protected function isServiceReferenceFieldVisible(Service $service, string $field, bool $default = true): bool
    {
        $report = Arr::get($service->options, 'report');

        if ($report) {
            $defaultBehavior = Arr::get($report, 'visibility');

            // prevent default behavior is null
            if ($defaultBehavior === null) {
                $defaultBehavior = Visibility::Visible->value;
            }

            $fields = Arr::get($report, 'fields');

            if ($fields) {
                $fieldsByKey = Arr::keyBy($fields, 'field');
                $fieldVisible = Arr::get($fieldsByKey, "$field.visible");

                if ($fieldVisible !== null) {
                    return $fieldVisible;
                }
            }

            return $defaultBehavior === Visibility::Visible->value;
        }

        return $default;
    }
}
