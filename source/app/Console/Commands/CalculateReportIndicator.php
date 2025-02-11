<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;

class CalculateReportIndicator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-report-indicator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $report = new ReportService();

        $report
            ->make('ЛН', '2024-02-01')
            ->addIndicatorByCode('INDICATOR_cF4bqE')
            ->extended();

        $v1 = $report->calculate();
        // $v11 = $v1[0]['debug']['data']->toArray();
        // $v2 = $report->debugService(17);
    }
}
