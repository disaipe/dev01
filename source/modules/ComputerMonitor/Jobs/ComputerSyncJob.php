<?php

namespace App\Modules\ComputerMonitor\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Core\Reference\ReferenceManager;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class ComputerSyncJob extends ModuleScheduledJob
{
    private string $source;

    private string $sourceId;

    private ?Model $record;

    public function __construct(int $id)
    {
        parent::__construct();

        $this->sourceId = $id;
        $this->source = $this->getModule()->getConfig('source');

        /** @var ReferenceManager $references */
        $references = app('references');
        $reference = $references->getByName($this->source);

        if (! $reference) {
            throw new Exception(__(
                'pcmon::messages.job.computers sync.errors.source failed',
                [
                    'source' => $this->source,
                ]
            ));
        }

        $sourceModel = $reference->getModel();

        if ($sourceModel) {
            $this->record = $sourceModel::query()->find($this->sourceId);
        }
    }

    /**
     * @throws Exception
     */
    public function work(): ?array
    {
        if (! $this->record) {
            throw new Exception(__(
               'pcmon::messages.job.computer sync.errors.source not found',
               [
                   'source' => $this->source,
                   'id' => $this->sourceId,
               ]
            ));
        }

        return $this->processComputer($this->record);
    }

    public function getDescription(): ?string
    {
        return __('pcmon::messages.job.computer sync.title');
    }

    protected function processComputer(Model $record): array
    {
        $module = $this->getModule();

        $baseUrl = $module->getConfig('base_url');
        $secret = $module->getConfig('secret');
        $dnsField = $module->getConfig('dns_field');
        $method = '/computer_sync_job';

        $id = $record->getKey();
        $hostname = $record->getAttribute($dnsField);

        $headers = [
            'Content-Type' => 'application/json',
            'X-SECRET' => $secret,
            'X-APP-AUTH' => Crypt::encryptString($id.'|'.config('app.url')),
        ];

        $response = Http::baseUrl($baseUrl)
            ->withHeaders($headers)
            ->post($method, [
                'Id' => $id,
                'Host' => $hostname,
            ]);

        return $response->json();
    }
}
