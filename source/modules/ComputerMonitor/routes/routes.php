<?php

use App\Modules\ComputerMonitor\Models\ADComputerEntryStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;

Route::post('/api/module/pcmon/post-result', function (Request $request) {
    $auth = $request->header('X-APP-AUTH');

    if (! $auth) {
        abort(401);
    }

    try {
        $decryptedAuth = Crypt::decryptString($auth);
    } catch (\Exception $e) {
        abort(401);
    }

    $result = $request->all();

    $id = Arr::get($result, 'Id');
    $hosts = Arr::get($result, 'Hosts');

    [$_id, $_appUrl] = explode('|', $decryptedAuth);

    if (($id != $_id && $_id !== 'batched') || ! Str::startsWith($_appUrl, config('app.url'))) {
        abort(401);
    }

    $recordsToInsert = [];

    if ($id) {
        $status = Arr::get($result, 'Status');
        $userName = Arr::get($result, 'UserName');

        if ($status && $userName) {
            $userNameParts = explode('\\', $userName);
            $userName = array_pop($userNameParts);

            $recordsToInsert[] = [
                'ad_computer_entry_id' => $id,
                'username' => $userName,
            ];
        }
    } elseif ($_id == 'batched' && is_array($hosts)) {
        foreach ($hosts as $host) {
            $hostId = Arr::get($host, 'Id');
            $userName = Arr::get($host, 'UserName');

            if ($hostId && $userName) {
                $userNameParts = explode('\\', $userName);
                $userName = array_pop($userNameParts);

                $recordsToInsert[] = [
                    'ad_computer_entry_id' => $hostId,
                    'username' => $userName,
                ];
            }
        }
    }

    if (count($recordsToInsert)) {
        ADComputerEntryStatus::query()->insert($recordsToInsert);
    }
});
