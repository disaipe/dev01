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

    protected ?string $period;

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

    public function make(string $templateId, string $companyCode, string $period = null): array
    {
        $this->company = Company::query()->where('code', '=', $companyCode)->first();
        $this->period = $period;

        $cellReplacements = [];
        $errors = [];

        // Process values cells
        $values = $this->generate($templateId, $companyCode);
        foreach ($values as $serviceId => $value) {
            $serviceName = Arr::get($value, 'service.name');

            if ($error = Arr::get($value, 'error')) {
                $errors[] = [
                    'service_id' => $serviceId,
                    'service_name' => $serviceName,
                    'message' => $error,
                ];
            }

            $cellReplacements["SERVICE#$serviceId#NAME"] = $serviceName;
            $cellReplacements["SERVICE#$serviceId#COUNT"] = Arr::get($value, 'value') ?? 0;
            $cellReplacements["SERVICE#$serviceId#PRICE"] = Arr::get($value, 'price') ?? 0;
        }

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

    protected function generate(string $templateId, string $companyCode): array
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
                    [,$id] = explode('#', $v);

                    if ($id) {
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
                    /** @var Indicator $indicator */
                    $query = $this->getBaseQuery($indicator->model);

                    $result = $indicator
                        ->addContext($this->getContext())
                        ->applyScopes($query)
                        ->exec($query);

                    $results[$serviceKey]['value'] = $result;
                } catch (\Exception $e) {
                    $results[$serviceKey]['error'] = $e->getMessage();
                }
            } else {
                $results[$serviceKey]['error'] = 'Индикатор не найден, расчет показателей невозможен';
            }
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

        throw new \Exception("Модель данных '{$model}' не определена");
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

        if (! $priceList) {
            throw new \Exception('Прайс лист не найден');
        }

        $this->priceList = $priceList;
    }

    protected function addPrices(array &$values): void
    {
        $priceByService = $this->priceList->values->pluck('value', 'service_id');

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
