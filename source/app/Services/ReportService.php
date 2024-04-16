<?php

namespace App\Services;

use App\Core\Enums\ReportContextConstant;
use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceManager;
use App\Facades\Config;
use App\Models\Company;
use App\Models\Contract;
use App\Models\PriceList;
use App\Models\ReportTemplate;
use App\Models\Service;
use Carbon\Carbon;
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
    protected bool $extended = false;

    protected string $companyCode;

    protected ?string $templateId = null;

    protected Company $company;

    protected ?string $period;

    protected ReferenceManager $referenceManager;

    protected ?ReportTemplate $template = null;

    protected Collection $priceListValues;

    protected ?Contract $contract = null;

    protected Collection $services;

    protected Spreadsheet $spreadsheet;

    protected Collection $indicators;

    protected Collection $values;

    protected array $config;

    protected array $commonExcludedFields = [];

    protected array $excludedFieldsByReference = [];

    protected IndicatorManager $indicatorManager;

    public function __construct()
    {
        $this->referenceManager = app('references');
        $this->indicatorManager = app('indicators');

        $this->indicators = collect([]);

        $this->config = Config::get('report');
    }

    public function make(string $companyCode, string $period = null): self
    {
        $this->companyCode = $companyCode;
        $this->period = $period ?? Carbon::today()->format('Y-m');

        $this->company = Company::query()->where('code', '=', $companyCode)->first();
        $this->services = Service::query()->get()->keyBy('id')->collect();

        return $this;
    }

    public function setPriceList(PriceList $priceList): self
    {
        $this->priceListValues = $priceList->values->collect();

        return $this;
    }

    public function setContract(Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function setTemplate(string $templateId): self
    {
        $this->templateId = $templateId;
        $this->template = ReportTemplate::query()->find($this->templateId);

        $this->getPriceListFromTemplate();
        $this->getContractFromTemplate();
        $this->getIndicatorsFromTemplate();

        return $this;
    }

    public function extended(bool $extend = true): self
    {
        $this->extended = $extend;

        $this->prepareDetailedReportExcludedFields();

        return $this;
    }

    public function addIndicatorByCode($code): self
    {
        $indicator = $this->indicatorManager->getByCode($code);

        if ($indicator) {
            $this->indicators->add($indicator);
        }

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

        $result = [
            'errors' => $errors,
            'values' => array_merge($cellReplacements, $this->getContext()),
            'xlsx' => $this->template->content,
        ];

        if ($this->extended) {
            $result['debug'] = $this->values
                // debug only items with not-null value
                ->filter(fn ($item) => Arr::get($item, 'value'))
                ->mapWithKeys($this->makeDebugRowsForService(...));
        }

        return $result;
    }

    public function download(string $templateId, string $companyCode): false|string
    {
        $this->generate($templateId, $companyCode);

        return $this->getTemplateWithData(true);
    }

    public function calculate(): Collection
    {
        return $this->calculateIndicators($this->indicators);
    }

    public function generate(): self
    {
        $this->values = $this->calculate();
        $this->addPrices($this->values);

        return $this;
    }

    public function debugService(int|string $serviceId): array
    {
        $service = $this->services->get($serviceId);

        /** @var Indicator $indicator */
        $indicator = $this->getServiceIndicators($service)[$serviceId];

        return $this->debugIndicator($indicator);
    }

    public function debugIndicator(Indicator|string $indicator): ?array
    {
        $indicatorInstance = null;

        if (is_string($indicator)) {
            $indicatorInstance = $this->indicatorManager->getByCode($indicator);
        } else if (get_class($indicator) === Indicator::class) {
            $indicatorInstance = $indicator;
        }

        if (! $indicatorInstance) {
            return null;
        }

        return $indicatorInstance
            ->setContext($this->getContext())
            ->debug();
    }

    protected function prepareDetailedReportExcludedFields(): void
    {
        $this->commonExcludedFields = Arr::get($this->config, 'fields.exclude') ?? [];

        $excludedFieldsInReferences = Arr::get($this->config, 'fields.references.exclude') ?? [];
        $this->excludedFieldsByReference = Arr::pluck($excludedFieldsInReferences, 'fields', 'reference');
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
    protected function getIndicatorsFromTemplate(): self
    {
        $templateServices = $this->getTemplateServices();
        $this->indicators = $this->getServiceIndicators($templateServices);

        return $this;
    }

    /**
     * @param  Service|Service[]  $service
     */
    protected function getServiceIndicators(Service|array $service): Collection
    {
        $services = Arr::wrap($service);
        $registeredIndicators = $this->indicatorManager->getIndicators();

        $indicators = collect([]);

        foreach ($services as $service) {
            if ($service) {
                $indicator = Arr::get($registeredIndicators, $service->indicator_code);
                $indicators->put($service->getKey(), $indicator);
            }
        }

        return $indicators;
    }

    protected function calculateIndicators(Collection|array $indicators): Collection
    {
        $results = collect([]);

        foreach ($indicators as $serviceKey => $indicator) {
            $service = Arr::get($this->services, $serviceKey);

            $serviceValue = [
                'service' => $service,
                'indicator' => $indicator,
            ];

            if (is_a($indicator, Indicator::class)) {
                try {
                    $serviceValue['value'] = $this->calculateIndicator($indicator);

                    if ($this->extended) {
                        $serviceValue['debug'] = $this->debugIndicator($indicator);
                    }
                } catch (\Exception $e) {
                    $serviceValue['error'] = $e->getMessage();
                }
            } else {
                $serviceValue['error'] = 'Индикатор не найден, расчет показателей невозможен';
            }

            $results[$serviceKey] = $serviceValue;
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
            ->where(function (Builder $subQuery) {
                $subQuery
                    ->whereHas('companies', fn (Builder $query) => $query->whereKey($this->company->getKey()))
                    ->orWhere('is_default', '=', true);
            })
            ->get()
            ->collect()
            ->sortBy('is_default')
            ->first();

        if (! $priceList) {
            throw new \Exception('Прайс лист не найден');
        }

        return $this->setPriceList($priceList);
    }

    protected function addPrices(Collection|array &$values): void
    {
        $priceByService = $this->priceListValues->pluck('value', 'service_id');

        foreach ($values as $k => $value) {
            if (! Arr::has($value, 'error')) {
                $serviceId = $value['service']->id;
                $value['price'] = Arr::get($priceByService, $serviceId, 0);
            }
            $values[$k] = $value;
        }
    }

    protected function getContractFromTemplate(): self
    {
        if ($this->template) {
            $this->contract = Contract::query()
                ->where('company_id', '=', $this->company->getKey())
                ->where('service_provider_id', '=', $this->template->service_provider_id)
                ->where('is_actual', '=', true)
                ->first();
        }

        return $this;
    }

    protected function getContext(): array
    {
        $period = Carbon::make($this->period);
        $contract = $this->contract;

        return [
            ReportContextConstant::PERIOD->name => $period,
            ReportContextConstant::PERIOD_RAW->name => $this->period,
            ReportContextConstant::PERIOD_YEAR->name => strval($period->year),
            ReportContextConstant::PERIOD_MONTH->name => strval($period->month),
            ReportContextConstant::PERIOD_MONTH_NAME->name => $period->getTranslatedMonthName(),
            ReportContextConstant::PERIOD_YEAR_MONTH->name => $period->format('Y-m'),
            ReportContextConstant::COMPANY_ID->name => $this->company->getKey(),
            ReportContextConstant::COMPANY_CODE->name => $this->company->code,
            ReportContextConstant::COMPANY_NAME->name => $this->company->name,
            ReportContextConstant::COMPANY_NAME_FULL->name => $this->company->fullname ?? $this->company->name,
            ReportContextConstant::CONTRACT_NUMBER->name => $contract?->number ?? '',
            ReportContextConstant::CONTRACT_DATE->name => $contract?->date?->toDateString() ?? '',
        ];
    }

    protected function makeDebugRowsForService(array $item): array
    {
        /** @var ?Collection $data */
        $data = Arr::get($item, 'debug.data');
        $columns = Arr::get($item, 'debug.columns', []);

        /** @var ?string $refName */
        $refName = Arr::get($item, 'debug.reference');

        /** @var ?Indicator $indicator */
        $indicator = Arr::get($item, 'indicator');

        // hide reference columns what is not visible
        if (! count($columns) && $refName) {
            $schema = $this->referenceManager->getByName($refName)?->getSchema();

            if ($schema) {
                $columns = Arr::where(
                    $schema,
                    fn (ReferenceFieldSchema $field, string $name) => ! $this->isReferenceFieldExcluded($refName, $name, $field)
                );

                if (! count($columns)) {
                    $columns = $schema;
                }

                $columns = Arr::map($columns, fn (ReferenceFieldSchema $col) => $col->getLabel());

                $keys = array_keys($columns);
                $data = $data->map(fn (Model $row) => $row->only($keys));
            }
        }

        // apply expression column mutator
        if ($indicator->mutator) {
            $columnOption = $indicator->expression->getOptions('column');

            if ($columnOption) {
                $data = $data->map(function ($row) use ($indicator, $columnOption) {
                    if (isset($row[$columnOption])) {
                        $row[$columnOption] = $indicator->mutateValue($row[$columnOption]);
                    }
                    return $row;
                });
            }
        }

        return [$item['service']->getKey() => [
            'service' => [
                'id' => Arr::get($item, 'service.id'),
                'name' => Arr::get($item, 'service.name')
            ],
            'columns' => $columns,
            'rows' => $data,
        ]];
    }

    protected function isReferenceFieldExcluded(string $reference, string $name, ReferenceFieldSchema $field): bool {
        if (! $field->isVisible()) {
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
