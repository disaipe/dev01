<?php

namespace App\Modules\FileStorageMonitor\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\FileStorageMonitor\Enums\FileStorageSyncStatus;
use App\Modules\FileStorageMonitor\Models\FileStorage;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class FileStorageSyncJob extends ModuleScheduledJob
{
    private ?FileStorage $storage;

    private int $storageId;

    public function __construct(int $storageId)
    {
        parent::__construct();

        $this->storageId = $storageId;

        $this->storage = FileStorage::query()->find($this->storageId);
    }

    /**
     * @throws \Exception
     */
    public function work(): ?array
    {
        if (! $this->storage) {
            throw new \Exception(__(
                'fsmonitor::messages.job.storage sync.errors.storage not found',
                ['id' => $this->storageId]
            ));
        }

        return $this->processStorage($this->storage);
    }

    public function getDescription(): ?string
    {
        return __('fsmonitor::messages.job.storage sync.title', ['storage' => $this->storage->name]);
    }

    protected function processStorage(FileStorage $storage): array
    {
        $config = $this->getModuleConfig();

        $baseUrl = Arr::get($config, 'base_url');
        $secret = Arr::get($config, 'secret');
        $method = '/get';

        $id = $storage->getKey();
        $path = $storage->path;

        $headers = [
            'Content-Type' => 'application/json',
            'X-SECRET' => $secret,
            'X-APP-AUTH' => Crypt::encryptString($id.'|'.config('app.url')),
        ];

        $resp = Http::baseUrl($baseUrl)
            ->withHeaders($headers)
            ->post($method, [
                'Id' => $id,
                'Path' => $path,
            ]);

        $lastError = null;
        $lastSync = Carbon::now();
        $lastStatus = FileStorageSyncStatus::Queued;

        if ($resp->ok()) {
            $status = $resp->json('Status');

            if (! $status) {
                $lastError = 'API Error';
                $lastStatus = FileStorageSyncStatus::Failed;
            }
        } else {
            $lastError = $resp->reason();
            $lastStatus = FileStorageSyncStatus::Failed;
        }

        $storage->updateQuietly([
            'last_error' => $lastError,
            'last_sync' => $lastSync,
            'last_status' => $lastStatus,
        ]);

        return [
            'error' => $lastError,
            'status' => $lastStatus,
        ];
    }
}
