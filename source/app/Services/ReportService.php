<?php

namespace App\Services;

use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Report\Expression\CountExpression;
use App\Core\Report\Expression\Expression;
use App\Core\Report\Expression\SumExpression;
use App\Models\Indicator as CustomExpression;
use App\Models\ReportTemplate;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ReportService
{
    protected string $companyCode;
    protected string $templateId;
    protected ReportTemplate $template;
    protected Collection $services;
    protected Spreadsheet $spreadsheet;

    protected array $foundCells;

    public function make($companyCode, $templateId): array
    {
        $values = $this->generate($companyCode, $templateId);

        $cellReplacements = [];
        foreach ($values as $serviceId => $value) {
            $cellReplacements["SERVICE#$serviceId#NAME"] = Arr::get($value, 'service.name');
            $cellReplacements["SERVICE#$serviceId#COUNT"] = Arr::get($value, 'value');
        }

        return [
            'values' => $cellReplacements,
            'xlsx' => $this->template->content
        ];
    }

    public function download($companyCode, $templateId): false|string
    {
        $this->generate($companyCode, $templateId);
        return $this->getTemplateWithData(true);
    }

    protected function generate($companyCode, $templateId): array
    {
        $this->companyCode = $companyCode;
        $this->templateId = $templateId;
        $this->services = Service::query()->get()->keyBy('id')->collect();

        $this->prepareTemplate();
        $indicators = $this->getTemplateServices();
        $values = $this->calculateIndicators($indicators);
        $this->applyIndicatorsToTemplate($values);

        return $values;
    }

    protected function prepareTemplate(): void
    {
        $this->template = ReportTemplate::query()->find($this->templateId)->first();

        $templateFileName = tempnam('/tmp', 'rep_');
        file_put_contents($templateFileName, base64_decode($this->template->content));

        $reader = new XlsxReader();
        $this->spreadsheet = $reader->load($templateFileName);
    }

    protected function getTemplateServices(): array
    {
        $this->foundCells = [];
        $foundServices = [];

        $worksheet = $this->spreadsheet->getActiveSheet();

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $v = $cell->getValue();

                if ($v && Str::startsWith($v, 'SERVICE')) {
                    [,$id,$type] = explode('#', $v);

                    if ($type === 'NAME') {
                        $foundServices []= Arr::get($this->services, $id);
                    }
                }
            }
        }

        /** @var IndicatorManager $indicatorsManager */
        $indicatorsManager = app('indicators');
        $registeredIndicators = $indicatorsManager->getIndicators();

        $indicators = [];

        foreach ($foundServices as $service) {
            /** @var Service $service */

            $indicators[$service->getKey()] = $registeredIndicators[$service->indicator_code];
        }

        return $indicators;
    }

    protected function calculateIndicators($indicators): array
    {
        $results = [];
        foreach ($indicators as $serviceKey => $indicator) {
            /** @var Indicator $indicator */

            $scopedQuery = $this->getScopedBaseQuery($indicator->model, $this->companyCode);
            $expressionQuery = $indicator->query
                ? ($indicator->query)($scopedQuery)
                : $scopedQuery;

            $result = $indicator->expression->exec($expressionQuery);

            $service = Arr::get($this->services, $serviceKey);

            $results[$serviceKey] = [
                'service' => $service,
                'indicator' => $indicator,
                'value' => $result
            ];
        }

        return $results;
    }

    protected function getBaseQuery(string $model): Builder
    {
        if (is_subclass_of($model, Model::class)) {
            return $model::query();
        }

        throw new \Exception('Wrong service model');
    }

    protected function getScopedBaseQuery(string $model, string $companyCode): Builder
    {
        return $this->getBaseQuery($model)->company($companyCode);
    }

//    protected function normalize(array &$service)
//    {
//        $definition = Arr::get($service, 'expression');
//
//        if (is_subclass_of($definition, Expression::class)) {
//            return;
//        }
//
//        if (is_string($definition)) {
//            /** @var CustomExpression $expressionRecord */
//            $expressionRecord = CustomExpression::query()->where('code', '=', $definition)->first();
//
//            if ($expressionRecord) {
//                [$data] = Arr::get($expressionRecord->schema, 'values');
//                $expressionType = Str::start(Arr::get($data, 'type'), 'App\Core\Report\Expression\\');
//
//                if (class_exists($expressionType)) {
//                    $args = Arr::get($data, 'data');
//
//                    $service['model'] = Arr::get($expressionRecord->schema, 'reference');
//                    $service['expression'] = new $expressionType(...$args);
//                }
//            }
//        }
//    }

    /**
     * NOT USED
     *
     * Modifying template break cell styles on front-end exceljs library
     *
     * @param $values
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function applyIndicatorsToTemplate($values): void
    {
        $worksheet = $this->spreadsheet->getActiveSheet();

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $v = $cell->getValue();

                if ($v && Str::startsWith($v, 'SERVICE')) {
                    [,$id,$type] = explode('#', $v);

                    if ($type === 'NAME') {
                        $service = Arr::get($this->services, $id);
                        $cell->setValue($service->name);
                    } else if ($type === 'COUNT') {
                        $value = Arr::get($values, "{$id}.value");
                        $cell->setValue($value);
                    }
                }
            }
        }
    }

    /**
     * NOT USED
     *
     * Modifying template break cell styles on front-end exceljs library
     *
     * @param bool $getFile
     * @return false|string
     * @throws Exception
     */
    protected function getTemplateWithData($getFile = false): false|string
    {
        $fileName = tempnam('/tmp', 'rep_');

        $xlsx = new XlsxWriter($this->spreadsheet->copy());
        $xlsx->save($fileName);

        if ($getFile) {
            return $fileName;
        }

        $content = file_get_contents($fileName);
        unlink($fileName);

        return base64_encode($content);
    }
}