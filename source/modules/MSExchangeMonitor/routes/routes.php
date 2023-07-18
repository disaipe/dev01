<?php

use App\Modules\MSExchangeMonitor\Models\MSExchangeMailboxStat;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::any('/api/module/msexmonitor/post-result', function (Request $request) {
    $auth = $request->header('X-APP-AUTH');

    if (!$auth) {
        abort(401);
    }

    try {
        $decryptedAuth = Crypt::decryptString($auth);
    } catch (\Exception $e) {
        abort(401);
    }

    $result = $request->all();

    $id = Arr::get($result, 'Id');

    [$_id, $_appUrl] = explode('|', $decryptedAuth);

    if ($id != $_id || ! Str::startsWith($_appUrl, config('app.url'))) {
        abort(401);
    }

    $status = Arr::get($result, 'Status');
    $errorMessage = Arr::get($result, 'Error');
    $items = Arr::get($result, 'Items');

    if (!$status) {
        Log::error("[MSExchangeMailboxStat] Error: $errorMessage");

        abort(400);
    }

    DB::table(MSExchangeMailboxStat::query()->getModel()->getTable())->truncate();

    $itemsToInsert = Arr::map($items, function (array $item) {
        return [
            'guid' => Arr::get($item, 'Id'),
            'display_name' => Arr::get($item, 'DisplayName'),
            'total_item_size' => Arr::get($item, 'TotalItemSize', 0),
            'total_item_count' => Arr::get($item, 'TotalItemCount', 0),
        ];
    });

    MSExchangeMailboxStat::query()->insert($itemsToInsert);
});
