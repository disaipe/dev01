<?php

namespace App\Services;

use App\Core\Enums\ReportContextConstant;
use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Reference\ReferenceManager;
use App\Models\Company;
use App\Models\Contract;
use App\Models\PriceList;
use App\Models\ReportTemplate;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

    protected ?string $period;

    protected ReferenceManager $referenceManager;

    protected ReportTemplate $template;

    protected Collection $priceListValues;

    protected Collection $services;

    protected Spreadsheet $spreadsheet;

    protected array $values;

    public function __construct()
    {
        $this->referenceManager = app('references');
    }

    public function make(string $companyCode, string $period = null): self
    {
        $this->companyCode = $companyCode;
        $this->period = $period;

        $this->company = Company::query()->where('code', '=', $companyCode)->first();
        $this->services = Service::query()->get()->keyBy('id')->collect();

        return $this;
    }

    public function setTemplate(string $templateId): self
    {
        $this->templateId = $templateId;
        $this->template = ReportTemplate::query()->find($this->templateId);

        $this->getPriceListFromTemplate();

        return $this;
    }

    public function getTemplateData(): array
    {
        $cellReplacements = [];
        $errors = [];
        $total = 0;

        foreach ($this->values as $serviceId => $value) {
            $serviceName = Arr::get($value, 'service.name');

            if ($error = Arr::get($value, 'error')) {
                $errors[] = [
                    'service_id' => $serviceId,
                    'service_name' => $serviceName,
                    'message' => $error,
                ];
            }

            $count = Arr::get($value, 'value') ?? 0;
            $price = Arr::get($value, 'price') ?? 0;

            $cellReplacements["SERVICE#$serviceId#NAME"] = $serviceName;
            $cellReplacements["SERVICE#$serviceId#COUNT"] = $count;
            $cellReplacements["SERVICE#$serviceId#PRICE"] = $price;

            $total += $count * $price;
        }

        // Process totals
        $vat = ($this->priceList->service_provider->vat ?? 0) / 100 * $total;

        $cellReplacements['TOTAL'] = $total;
        $cellReplacements['TOTAL_VAT'] = $vat;
        $cellReplacements['TOTAL_WITH_VAT'] = $total + $vat;

        // Process contract cells
        $contract = $this->getContract();
        $cellReplacements['CONTRACT#NUMBER'] = $contract?->number ?? '';
        $cellReplacements['CONTRACT#DATE'] = $contract?->date?->toDateString() ?? '';

        return [
            'errors' => $errors,
            'values' => $cellReplacements,
            'xlsx' => $this->template->content,
        ];
    }

    public function download(string $templateId, string $companyCode): false|string
    {
        $this->generate($templateId, $companyCode);

        return $this->getTemplateWithData(true);
    }

    public function generate(): self
    {
        $indicators = $this->getTemplateIndicators();
        $values = $this->calculateIndicators($indicators);
        $this->addPrices($values);

        $this->values = $values;

        return $this;
    }

    public function debugServiceIndicator(int|string $serviceId): array
    {
        $service = $this->services->get($serviceId);

        /** @var Indicator $indicator */
        $indicator = $this->getServiceIndicators($service)[$serviceId];

        return $indicator
            ->setContext($this->getContext())
            ->debug();
    }

    /**
     * Load template with the XLSX reader
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function prepareTemplate(): void
    {
        $templateFileName = tempnam('/tmp', 'rep_');
        file_put_contents($templateFileName, base64_decode($this->template->content));

        $reader = new XlsxReader();
        $this->spreadsheet = $reader->load($templateFileName);
    }

    /**
     * Scan template for service insertions
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function getTemplateServices(): array
    {
        $this->prepareTemplate();

        $foundServices = [];

        $worksheet = $this->spreadsheet->getActiveSheet();

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $v = $cell->getValue();

                if ($v && Str::startsWith($v, 'SERVICE')) {
                    [,$id] = explode('#', $v);

                    if ($id) {
                        $foundServices[] = Arr::get($this->services, $id);
                    }
                }
            }
        }

        return $foundServices;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function getTemplateIndicators(): array
    {
        $templateServices = $this->getTemplateServices();

        return $this->getServiceIndicators($templateServices);
    }

    /**
     * @param  Service|Service[]  $service
     */
    protected function getServiceIndicators(Service|array $service): array
    {
        $services = Arr::wrap($service);

        /** @var IndicatorManager $indicatorsManager */
        $indicatorsManager = app('indicators');
        $registeredIndicators = $indicatorsManager->getIndicators();

        $indicators = [];

        foreach ($services as $service) {
            $indicator = Arr::get($registeredIndicators, $service->indicator_code);
            $indicators[$service->getKey()] = $indicator;
        }

        return $indicators;
    }

    protected function calculateIndicators(array $indicators): array
    {
        $results = [];
        foreach ($indicators as $serviceKey => $indicator) {
            $service = Arr::get($this->services, $serviceKey);

            $results[$serviceKey] = [
                'service' => $service,
                'indicator' => $indicator,
            ];

            if ($indicator) {
                try {
                    $results[$serviceKey]['value'] = $this->calculateIndicator($indicator);
                } catch (\Exception $e) {
                    $results[$serviceKey]['error'] = $e->getMessage();
                }
            } else {
                $results[$serviceKey]['error'] = 'Индикатор не найден, расчет показателей невозможен';
            }
        }

        return $results;
    }

    protected function calculateIndicator(Indicator $indicator): float
    {
        return $indicator
            ->setContext($this->getContext())
            ->exec();
    }

    /**
     * @throws \Exception
     */
    protected function getPriceListFromTemplate(): self
    {
        $serviceProviderId = $this->template->service_provider_id;

        /** @var ?PriceList $priceList */
        $priceList = PriceList::query()
            ->where('service_provider_id', '=', $serviceProviderId)
            ->where(fn (Builder $query) => $query
                ->where('company_id', '=', $this->company->getKey())
                ->orWhere('is_default', '=', true)
            )
            ->first();

        if (! $priceList) {
            throw new \Exception('Прайс лист не найден');
        }

        $this->priceListValues = $priceList->values->collect();

        return $this;
    }

    protected function addPrices(array &$values): void
    {
        $priceByService = $this->priceListValues->pluck('value', 'service_id');

        foreach ($values as &$value) {
            if (! Arr::has($value, 'error')) {
                $serviceId = $value['service']->id;
                $value['price'] = Arr::get($priceByService, $serviceId, 0);
            }
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

    protected function getContext(): array
    {
        $period = Carbon::make($this->period);

        return [
            ReportContextConstant::PERIOD->name => $period,
            ReportContextConstant::PERIOD_RAW->name => $this->period,
            ReportContextConstant::PERIOD_YEAR->name => $period->year,
            ReportContextConstant::PERIOD_MONTH->name => $period->month,
            ReportContextConstant::COMPANY_ID->name => $this->company->getKey(),
            ReportContextConstant::COMPANY_CODE->name => $this->company->code,
        ];
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
