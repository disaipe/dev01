<?php

namespace App\Modules\MSExchangeMonitor\Jobs;

use App\Core\Module\ModuleScheduledJob;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MSExchangeStatsSyncJob extends ModuleScheduledJob
{
    public function work(): ?array
    {
        $config = $this->getModuleConfig();

        $baseUrl = Arr::get($config, 'base_url');
        $secret = Arr::get($config, 'secret');
        $method = '/get';

        $id = Str::uuid()->toString();

        $headers = [
            'Content-Type' => 'application/json',
            'X-SECRET' => $secret,
            'X-APP-AUTH' => Crypt::encryptString($id.'|'.config('app.url')),
        ];

        $resp = Http::baseUrl($baseUrl)
            ->withHeaders($headers)
            ->post($method, [
                'Id' => $id,
            ]);

        $lastError = null;
        $lastStatus = true;

        if ($resp->ok()) {
            $status = $resp->json('Status');

            if (! $status) {
                $lastError = 'API Error';
                $lastStatus = false;
            }
        } else {
            $lastError = $resp->reason();
            $lastStatus = false;
        }

        return [
            'error' => $lastError,
            'status' => $lastStatus,
        ];
    }

    public function getDescription(): ?string
    {
        return __('msexmonitor::messages.job.mailbox size sync.title');
    }
}
