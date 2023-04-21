<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $models = $request->input('models');

        $response = [];

        if (is_string($models)) {
            $models = explode(',', $models);
        }

        foreach ($models as $modelName) {
            $model = resolveModel($modelName);
            $response[$modelName] = $model->newQuery()->get();
        }

        return new JsonResponse([
            'status' => true,
            'data' => $response,
        ]);
    }
}
