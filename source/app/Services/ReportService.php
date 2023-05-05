<?php

namespace App\Services;

use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Reference\ReferenceManager;
use App\Models\Company;
use App\Models\Contract;
use App\Models\PriceList;
use App\Models\ReportTemplate;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class ReportService
{
    protected string $companyCode;

    protected string $templateId;

    protected Company $company;

    protected ReferenceManager $referenceManager;

    protected ReportTemplate $template;

    protected PriceList $priceList;

    protected Collection $services;

    protected Spreadsheet $spreadsheet;

    protected array $foundCells;

    public function __construct()
    {
        $this->referenceManager = app('references');
    }

    public function make($companyCode, $templateId): array
    {
        $this->company = Company::query()->where('code', '=', $companyCode)->first();

        $cellReplacements = [];

        // Process values cells
        $values = $this->generate($companyCode, $templateId);
        foreach ($values as $serviceId => $value) {
            $cellReplacements["SERVICE#$serviceId#NAME"] = Arr::get($value, 'service.name');
            $cellReplacements["SERVICE#$serviceId#COUNT"] = Arr::get($value, 'value');
            $cellReplacements["SERVICE#$serviceId#PRICE"] = Arr::get($value, 'price');
        }

        // Process contract cells
        $contract = $this->getContract();
        $cellReplacements['CONTRACT#NUMBER'] = $contract?->number ?? '';
        $cellReplacements['CONTRACT#DATE'] = $contract?->date?->toDateString() ?? '';

        return [
            'values' => $cellReplacements,
            'xlsx' => $this->template->content,
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
        $this->getPriceList();
        $indicators = $this->getTemplateServices();
        $values = $this->calculateIndicators($indicators);
        $this->addPrices($values);

        return $values;
    }

    protected function prepareTemplate(): void
    {
        $this->template = ReportTemplate::query()->find($this->templateId);

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
                        $foundServices[] = Arr::get($this->services, $id);
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

            $result = $indicator->exec($scopedQuery);

            $service = Arr::get($this->services, $serviceKey);

            $results[$serviceKey] = [
                'service' => $service,
                'indicator' => $indicator,
                'value' => $result,
            ];
        }

        return $results;
    }

    protected function getBaseQuery(string $model): Builder
    {
        if (is_subclass_of($model, Model::class)) {
            return $model::query();
        }

        if ($reference = $this->referenceManager->getByName($model)) {
            $model = $reference->getModelInstance();

            return $model->query();
        }

        throw new \Exception('Wrong service model');
    }

    protected function getScopedBaseQuery(string $model, string $companyCode): Builder
    {
        return $this->getBaseQuery($model)->company($companyCode);
    }

    /**
     * @throws \Exception
     */
    protected function getPriceList(): void
    {
        $serviceProviderId = $this->template->service_provider_id;

        $priceList = PriceList::query()
            ->where('service_provider_id', '=', $serviceProviderId)
            ->where(fn (Builder $query) => $query
                    ->where('company_id', '=', $this->company->getKey())
                    ->orWhere('is_default', '=', true)
            )
            ->first();

        if (!$priceList) {
            throw new \Exception('Прайс лист не найден');
        }

        $this->priceList = $priceList;
    }

    protected function addPrices(array &$values): void
    {
        $priceByService = $this->priceList->values->pluck('value', 'service_id');

        foreach ($values as &$value) {
            $serviceId = $value['service']->id;
            $value['price'] = Arr::get($priceByService, $serviceId, 0);
        }
    }

    protected function getContract(): ?Contract
    {
        return Contract::query()
            ->where('company_id', '=', $this->company->getKey())
            ->where('service_provider_id', '=', $this->template->service_provider_id)
            ->where('is_actual', '=', true)
            ->first();
    }

    /**
     * NOT USED
     *
     * Modifying template break cell styles on front-end exceljs library
     *
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
                    } elseif ($type === 'COUNT') {
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
     * @param  bool  $getFile
     *
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
