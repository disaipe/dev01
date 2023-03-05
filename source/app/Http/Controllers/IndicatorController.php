<?php

namespace App\Http\Controllers;

use App\Core\Indicator\IndicatorManager;
use App\Http\Resources\IndicatorResource;
use Illuminate\Http\JsonResponse;

class IndicatorController extends Controller
{
    public function __invoke() {
        /** @var IndicatorManager $indicators */
        $indicatorManager = app('indicators');

        $indicators = $indicatorManager->getIndicators();

        return new JsonResponse([
           'status' => true,
           'data' => IndicatorResource::collection(array_values($indicators))
        ]);
    }
}
