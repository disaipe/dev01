<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyCode = $request->input('company');
        $reportTemplateId = $request->input('template');

        $report = new \App\Services\ReportService();

        try {
            $data = $report->make($companyCode, $reportTemplateId);

            return new JsonResponse([
                'status' => true,
                'data' => $data,
            ]);
        } catch (\Exception|\Error $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ]);
        }
    }
}
