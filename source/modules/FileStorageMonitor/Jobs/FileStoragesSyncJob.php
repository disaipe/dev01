<?php

namespace App\Modules\FileStorageMonitor\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\FileStorageMonitor\Models\FileStorage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class FileStoragesSyncJob extends ModuleScheduledJob
{
    public function work(): ?array
    {
        $storages = FileStorage::query()
            ->enabled()
            ->get();

        $config = $this->getModuleConfig();

        $baseUrl = Arr::get($config, 'base_url');
        $secret = Arr::get($config, 'secret');
        $method = '/get';

        $headers = [
            'Content-Type' => 'application/json',
            'X-SECRET' => $secret,
            'X-APP-AUTH' => Crypt::encryptString('batched|' . config('app.url')),
        ];

        $body = [
            'Paths' => Arr::map($storages->toArray(), function ($storage) {
                return [
                    'Id' => $storage['id'],
                    'Path' => $storage['path']
                ];
            })
        ];

        $resp = Http::baseUrl($baseUrl)
            ->withHeaders($headers)
            ->post($method, $body);

        if ($resp->ok()) {
            return [
                'Result' => 'Started: '.$storages->count(),
            ];
        }

        return [
            'Result' => 'Failed to post data to daemon',
            'Status' => $resp->status(),
            'Body' => $resp->body(),
            'Reason' => $resp->reason(),
        ];
    }

    public function getDescription(): ?string
    {
        return __('fsmonitor::messages.job.storages sync.title');
    }
}
