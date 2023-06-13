<?php

use App\Modules\FileStorageMonitor\Enums\FileStorageSyncStatus;
use App\Modules\FileStorageMonitor\Models\FileStorage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/api/module/fsmonitor/post-result', function (Request $request) {
    $auth = $request->header('X-APP-AUTH');

    if (!$auth) {
        abort(401);
    }

    $result = $request->all();

    $id = Arr::get($result, 'Id');
    $size = Arr::get($result, 'Size');
    $duration = Arr::get($result, 'Duration');

    try {
        $decryptedAuth = \Illuminate\Support\Facades\Crypt::decryptString($auth);
    } catch (\Exception $e) {
        abort(401);
    }

    [$_id, $_appUrl] = explode('|', $decryptedAuth);
    if (($id != $_id && $_id !== 'batched') || $_appUrl !== config('app.url')) {
        abort(401);
    }

    if ($id) {
        /** @var FileStorage $fileStorage */
        $fileStorage = FileStorage::query()->whereKey($id)->first();

        $fileStorage?->updateQuietly([
            'size' => $size,
            'last_sync' => Carbon::now(),
            'last_duration' => $duration,
            'last_error' => null,
            'last_status' => FileStorageSyncStatus::Ready,
        ]);
    }
});
