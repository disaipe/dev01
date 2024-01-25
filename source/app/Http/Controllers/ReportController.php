<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function makeReport(Request $request): JsonResponse
    {
        $companyCode = $request->input('company');
        $reportTemplateId = $request->input('template');
        $period = $request->input('period');

        $report = new ReportService();

        try {
            $data = $report
                ->make($companyCode, $period)
                ->setTemplate($reportTemplateId)
                ->generate()
                ->getTemplateData();

            return new JsonResponse([
                'status' => true,
                'data' => $data,
            ]);
        } catch (\Exception|\Error $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage(),
            ]);
        }
    }

    public function debugService(Request $request): JsonResponse
    {
        $companyCode = $request->input('company');
        $period = $request->input('period');
        $service = $request->input('service');

        $report = new ReportService();

        $data = $report
            ->make($companyCode, $period)
            ->debugServiceIndicator($service);

        return new JsonResponse([
            'status' => true,
            'data' => $data,
        ]);
    }
}
