<?php

namespace App\Http\Controllers;

use App\Models\PriceList;
use App\Models\PriceListValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PriceListController extends Controller
{
    const VALUE_SHORT_KEY = 'i';

    const VALUE_SHORT_SERVICE = 's';

    const VALUE_SHORT_VALUE = 'v';

    public function list(PriceList $priceList): JsonResponse
    {
        $packed = $priceList->values->map(function (PriceListValue $item) {
            return [
                self::VALUE_SHORT_KEY => $item->getKey(),
                self::VALUE_SHORT_SERVICE => $item->service_id,
                self::VALUE_SHORT_VALUE => $item->value,
            ];
        });

        $services = $priceList->serviceProvider?->services;

        return new JsonResponse([
            'status' => true,
            'data' => [
                'values' => $packed,
                'services' => $services,
            ],
        ]);
    }

    public function update(Request $request, PriceList $priceList): JsonResponse
    {
        $data = $request->input();

        $normalized = Arr::map($data, function ($item) {
            return [
                'id' => Arr::get($item, self::VALUE_SHORT_KEY),
                'service_id' => Arr::get($item, self::VALUE_SHORT_SERVICE),
                'value' => Arr::get($item, self::VALUE_SHORT_VALUE),
            ];
        });

        $priceList->values()->sync($normalized);

        return $this->list($priceList);
    }
}
