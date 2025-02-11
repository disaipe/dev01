<?php

namespace App\Http\Controllers;

use App\Http\Requests\MakeReportRequest;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function makeReport(MakeReportRequest $request): JsonResponse
    {
        $companyCode = $request->input('company');
        $reportTemplateId = $request->input('template');
        $period = $request->input('period');
        $extended = $request->boolean('extended');

        $report = new ReportService();

        try {
            $data = $report
                ->make($companyCode, $period)
                ->extended($extended)
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
                'data' => $e->getFile().'('.$e->getLine().'): '.$e->getMessage(),
            ]);
        }
    }
}
