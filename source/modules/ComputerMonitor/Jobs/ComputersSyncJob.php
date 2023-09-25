<?php

namespace App\Modules\ComputerMonitor\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Core\Reference\ReferenceManager;
use App\Core\Utils\QueryConditionsBuilder;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class ComputersSyncJob extends ModuleScheduledJob
{
    /**
     * @throws Exception
     */
    public function work(): ?array
    {
        $module = $this->getModule();

        $source = $module->getConfig('source');

        /** @var ReferenceManager $references */
        $references = app('references');
        $reference = $references->getByName($source);

        if (! $reference) {
            throw new Exception(__(
                'pcmon::messages.job.computers sync.errors.source failed',
                [
                    'source' => $source,
                ]
            ));
        }

        $chunkSize = $module->getConfig('chunk_size');
        $parallel = (bool)$module->getConfig('parallel');
        $dnsField = $module->getConfig('dns_field');
        $filters = $module->getConfig('filters');

        $sourceModel = $reference->getModel();

        $query = $sourceModel::query();

        if (method_exists($sourceModel, 'scopeEnabled')) {
            $query = $query->enabled();
        }

        if ($filters) {
            QueryConditionsBuilder::applyToQuery($query, $filters);
        }

        $computers = $query
            ->whereNotNull($dnsField)
            ->inRandomOrder()
            ->get();

        $chunks = $computers->chunk(intval($chunkSize) ?? 100);

        $baseUrl = $module->getConfig('base_url');
        $secret = $module->getConfig('secret');
        $method = '/computer_sync_job';

        $headers = [
            'Content-Type' => 'application/json',
            'X-SECRET' => $secret,
            'X-APP-AUTH' => Crypt::encryptString('batched|'.config('app.url')),
        ];

        foreach ($chunks as $chunk) {
            /** @var Collection $chunk */

            $body = [
                'Parallel' => $parallel,
                'Host' => null,
                'Hosts' => $chunk->map(function (Model $computer) use ($dnsField) {
                    return [
                        'Id' => $computer->getKey(),
                        'Host' => $computer->getAttribute($dnsField),
                    ];
                })->values(),
            ];

            Http::baseUrl($baseUrl)
                ->withHeaders($headers)
                ->post($method, $body);
        }

        return [
            'Status' => 'Job started',
        ];
    }

    public function getDescription(): ?string
    {
        return __('pcmon::messages.job.computers sync.title');
    }
}
