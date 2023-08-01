<?php

namespace App\Modules\DatabaseMonitor;

use App\Core\Enums\JobProtocolState;
use App\Core\Indicator\Indicator;
use App\Core\Indicator\IndicatorManager;
use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression\SumExpression;
use App\Filament\Components\CronExpressionInput;
use App\Filament\Components\FormButton;
use App\Forms\Components\RawHtmlContent;
use App\Models\JobProtocol;
use App\Modules\DatabaseMonitor\Commands\CheckDatabaseServerCommand;
use App\Modules\DatabaseMonitor\Commands\CheckDatabaseServersCommand;
use App\Modules\DatabaseMonitor\Jobs\DatabaseServersSyncJob;
use App\Modules\DatabaseMonitor\Models\Database;
use Cron\CronExpression;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;

class DatabaseMonitorServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'dbmon';

    public function init(): void
    {
        $this->loadMigrations();

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(DatabaseServerReference::class);
        $references->register(DatabaseReference::class);

        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');
        $indicators->register([
            Indicator::fromArray([
                'module' => 'DBMON',
                'code' => 'DBMON_DATABASE_SIZE_SUM',
                'name' => 'Размер баз данных',
                'model' => Database::class,
                'expression' => new SumExpression('size'),
                'mutator' => fn ($value) => round($value / 1024 / 1024, 2),
            ]),
        ]);

        $this->commands([
            CheckDatabaseServerCommand::class,
            CheckDatabaseServersCommand::class,
        ]);
    }

    public function getOptions(): array
    {
        return [
            'name' => __('dbmon::messages.name'),
            'description' => __('dbmon::messages.description'),
            'view' => [
                'config' => [
                    RawHtmlContent::make(function ($get) {
                        $out = '';

                        $lastSync = JobProtocol::query()
                            ->where('name', '=', DatabaseServersSyncJob::class)
                            ->where('state', '=', JobProtocolState::Ready->value)
                            ->orderByDesc('ended_at')
                            ->first();

                        if ($lastSync) {
                            $out = '<div class="text-right text-sm">'.
                                __('admin.last sync date', ['date' => $lastSync->ended_at]).
                                '</div>';
                        }

                        $syncEnabled = $get('DatabaseServerSync.enabled');
                        $schedule = $get('DatabaseServerSync.schedule');

                        if ($syncEnabled && $schedule) {
                            $expr = new CronExpression($schedule);
                            $nextDate = $expr->getNextRunDate();
                            $nextDateStr = $nextDate->format('Y-m-d H:i:s');

                            $out .= '<div class="text-right text-sm">'.
                                __('admin.next sync date', ['date' => $nextDateStr]).
                                '</div>';
                        }

                        return $out;
                    })
                        ->columnSpanFull(),

                    Tabs::make('configurationTabs')->tabs([
                        Tabs\Tab::make(__('admin.configuration'))->schema([
                            Section::make('SQL Server')
                                ->collapsed()
                                ->schema([
                                    TextInput::make('sqlserver.organization_prop')
                                        ->label(__('dbmon::messages.sqlserver.organization prop'))
                                        ->helperText(__('dbmon::messages.sqlserver.organization prop help')),
                                ]),

                            Section::make(__('dbmon::messages.job.databases sync.title'))
                                ->schema([
                                    RawHtmlContent::make(__('dbmon::messages.job.databases sync.description')),

                                    CronExpressionInput::make('DatabaseServerSync.schedule')
                                        ->label(__('admin.schedule')),

                                    Checkbox::make('DatabaseServerSync.enabled')
                                        ->label(__('admin.enabled')),

                                    FormButton::make('runAllServersJob')
                                        ->label(__('admin.run'))
                                        ->action(fn () => $this->runAllServersJob()),
                                ]),
                        ]),

                        Tabs\Tab::make(__('admin.description'))->schema([
                            View::make('dbmon::help'),
                        ]),
                    ]),
                ],
            ],
        ];
    }

    public function schedule(Schedule $schedule): void
    {
        $this->scheduleJob($schedule, new DatabaseServersSyncJob(), 'DatabaseServerSync');
    }

    public function runAllServersJob(): void
    {
        try {
            DatabaseServersSyncJob::dispatch();
            Filament::notify('success', __('admin.job started'));
        } catch (\Exception|\Error $e) {
            Filament::notify('danger', __('admin.job staring error'), $e->getMessage());
            Log::error($e);
        }
    }
}
